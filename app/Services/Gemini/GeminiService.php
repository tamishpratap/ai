<?php

namespace App\Services\Gemini;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiService
{
    public function createLiveToken(?string $apiKey = null): array
    {
        $apiKey = $this->resolveApiKey($apiKey);
        $this->ensureConfigured($apiKey);

        $now = now('UTC');
        $expiresAt = $now->copy()->addMinutes(min(20, max(5, config('gemini.live_session_minutes'))));

        try {
            $response = $this->client($apiKey)->post(
                config('gemini.api_version', 'v1alpha').'/auth_tokens',
                [
                    'uses' => 1,
                    'expireTime' => $expiresAt->toRfc3339String(),
                    'newSessionExpireTime' => $now->copy()->addMinute()->toRfc3339String(),
                ]
            );
        } catch (ConnectionException $exception) {
            Log::warning('Gemini ephemeral token connection failed', ['exception' => $exception::class]);
            throw new RuntimeException('AI voice service is temporarily unreachable.', 0, $exception);
        }

        if (! $response->successful() || blank($response->json('name'))) {
            $this->logProviderFailure('live_token', $response->status(), $response->json('error.status'));
            throw new RuntimeException($this->friendlyProviderError($response->status(), $response->json('error.status')));
        }

        return [
            'token' => $response->json('name'),
            'expires_at' => $expiresAt->toIso8601String(),
            'websocket_url' => 'wss://generativelanguage.googleapis.com/ws/google.ai.generativelanguage.'.config('gemini.api_version', 'v1alpha').'.GenerativeService.BidiGenerateContentConstrained',
            'setup' => $this->liveSetup(),
        ];
    }

    public function generate(array $contents, ?string $apiKey = null, ?string $model = null): array
    {
        $apiKey = $this->resolveApiKey($apiKey);
        $this->ensureConfigured($apiKey);
        $targetModel = $model ?: config('gemini.default_model', 'gemini-2.5-flash');

        $started = microtime(true);
        try {
            $response = $this->client($apiKey)->post(
                'v1beta/models/'.rawurlencode($targetModel).':generateContent',
                [
                    'systemInstruction' => ['parts' => [['text' => config('gemini.system_instruction')]]],
                    'contents' => $contents,
                    'generationConfig' => [
                        'maxOutputTokens' => config('gemini.max_output_tokens', 2048),
                    ],
                ]
            );

            // Fallback to gemini-1.5-flash if the requested newer model is not supported
            if ($response->status() === 404 && $targetModel !== 'gemini-1.5-flash') {
                $response = $this->client($apiKey)->post(
                    'v1beta/models/gemini-1.5-flash:generateContent',
                    [
                        'systemInstruction' => ['parts' => [['text' => config('gemini.system_instruction')]]],
                        'contents' => $contents,
                        'generationConfig' => [
                            'maxOutputTokens' => config('gemini.max_output_tokens', 2048),
                        ],
                    ]
                );
            }
        } catch (ConnectionException $exception) {
            Log::warning('Gemini text connection failed', ['exception' => $exception::class]);
            throw new RuntimeException('AI service is temporarily unreachable.', 0, $exception);
        }

        if (! $response->successful()) {
            $this->logProviderFailure('generate_content', $response->status(), $response->json('error.status'));
            throw new RuntimeException($this->friendlyProviderError($response->status(), $response->json('error.status')));
        }

        $text = collect($response->json('candidates.0.content.parts', []))->pluck('text')->filter()->implode('');
        if (blank($text)) {
            throw new RuntimeException('AI returned an empty response. Please try again.');
        }

        return [
            'text' => trim($text),
            'usage' => [
                'input_tokens' => $response->json('usageMetadata.promptTokenCount'),
                'output_tokens' => $response->json('usageMetadata.candidatesTokenCount'),
                'total_tokens' => $response->json('usageMetadata.totalTokenCount'),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
            ],
        ];
    }

    public function liveSetup(): array
    {
        return [
            'model' => 'models/'.config('gemini.live_model', 'gemini-2.0-flash-exp'),
            'generationConfig' => [
                'responseModalities' => ['AUDIO'],
                'speechConfig' => [
                    'voiceConfig' => ['prebuiltVoiceConfig' => ['voiceName' => config('gemini.voice', 'Kore')]],
                ],
                'thinkingConfig' => ['thinkingLevel' => 'minimal'],
            ],
            'systemInstruction' => ['parts' => [['text' => config('gemini.system_instruction')]]],
            'inputAudioTranscription' => new \stdClass,
            'outputAudioTranscription' => new \stdClass,
            'sessionResumption' => new \stdClass,
            'contextWindowCompression' => [
                'triggerTokens' => 25000,
                'slidingWindow' => ['targetTokens' => 12000],
            ],
        ];
    }

    public function resolveApiKey(?string $apiKey = null): ?string
    {
        $candidate = trim((string) ($apiKey ?: request()?->header('X-Gemini-Api-Key') ?: request()?->input('gemini_api_key') ?: session('USER_GEMINI_API_KEY') ?: config('gemini.api_key')));
        return filled($candidate) ? $candidate : null;
    }

    public function client(?string $apiKey = null): PendingRequest
    {
        $resolvedKey = $this->resolveApiKey($apiKey);

        return Http::baseUrl(rtrim(config('gemini.base_url', 'https://generativelanguage.googleapis.com'), '/'))
            ->acceptJson()->asJson()
            ->withHeaders(['x-goog-api-key' => $resolvedKey])
            ->connectTimeout(10)->timeout(config('gemini.timeout', 60))->retry(2, 300, throw: false);
    }

    public function ensureConfigured(?string $apiKey = null): void
    {
        $resolvedKey = $this->resolveApiKey($apiKey);
        if (blank($resolvedKey)) {
            throw new RuntimeException('Gemini API key is not configured. Please set your Gemini key in Settings.');
        }
    }

    private function friendlyProviderError(int $status, ?string $providerStatus): string
    {
        return match (true) {
            $status === 401 || $status === 403 => 'AI credentials are invalid. Please check your Gemini API key.',
            $status === 429 || $providerStatus === 'RESOURCE_EXHAUSTED' => 'AI usage limit reached for this key. Please try again later.',
            $status === 404 => 'The requested Gemini AI model is unavailable for this key.',
            $status >= 500 => 'Gemini AI service is temporarily unavailable.',
            default => 'AI could not complete the request. Please try again.',
        };
    }

    private function logProviderFailure(string $operation, int $status, ?string $providerStatus): void
    {
        Log::warning('Gemini request failed', compact('operation', 'status', 'providerStatus'));
    }
}
