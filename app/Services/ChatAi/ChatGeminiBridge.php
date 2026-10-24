<?php

namespace App\Services\ChatAi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatGeminiBridge
{
    /**
     * Generate an answer using the dedicated Chat AI Gemini integration.
     *
     * @param  string  $systemInstruction  Grounding instruction with VERIFIED_CONTEXT
     * @param  string  $userQuery          The user's question
     * @return string|null                 Returns AI text, or null if unreachable/unconfigured
     */
    public function generateAnswer(string $systemInstruction, string $userQuery): ?string
    {
        $apiKey = config('chat_ai.api_key');
        if (empty($apiKey)) {
            Log::info('[ChatGeminiBridge] CHAT_AI_GEMINI_API_KEY is not configured. Using deterministic fallback.');
            return null;
        }

        $model = config('chat_ai.default_model', 'gemini-3.5-flash-lite');
        $baseUrl = rtrim(config('chat_ai.base_url', 'https://generativelanguage.googleapis.com'), '/');
        $apiVersion = config('chat_ai.api_version', 'v1beta');
        $timeout = (int) config('chat_ai.timeout', 5);
        $maxTokens = (int) config('chat_ai.max_output_tokens', 450);
        $temperature = (float) config('chat_ai.temperature', 0.25);

        $endpoint = "{$baseUrl}/{$apiVersion}/models/{$model}:generateContent";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey,
            ])
            ->timeout($timeout)
            ->post($endpoint, [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemInstruction],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $userQuery],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => $maxTokens,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $answer = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($answer && trim($answer) !== '') {
                    return trim($answer);
                }
            }

            Log::warning('[ChatGeminiBridge] Gemini API returned error: HTTP ' . $response->status() . ' - ' . substr($response->body(), 0, 200));
            return null;
        } catch (\Throwable $e) {
            Log::warning('[ChatGeminiBridge] Gemini API connection failure: ' . $e->getMessage() . '. Triggering deterministic fallback.');
            return null;
        }
    }

    /**
     * Check if the Chat AI Gemini integration is configured with a key.
     */
    public function isConfigured(): bool
    {
        return !empty(config('chat_ai.api_key'));
    }
}
