<?php

namespace App\Services\Gemini;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiService
{
    /**
     * Create an ephemeral live auth token for Gemini Live WebSocket session.
     *
     * @param  string|null  $apiKey
     * @return array{token: string, expires_at: string, websocket_url: string, setup: array}
     *
     * @throws RuntimeException
     */
    public function createLiveToken(?string $apiKey = null): array
    {
        $resolvedKey = $this->resolveApiKey($apiKey);
        $this->ensureConfigured($resolvedKey);

        $now = now('UTC');
        $expiresAt = $now->copy()->addMinutes(min(20, max(5, (int) config('gemini.live_session_minutes', 15))));

        try {
            $response = $this->client($resolvedKey)->post(
                config('gemini.api_version', 'v1alpha').'/auth_tokens',
                [
                    'uses' => 1,
                    'expireTime' => $expiresAt->toRfc3339String(),
                    'newSessionExpireTime' => $now->copy()->addMinute()->toRfc3339String(),
                ]
            );
        } catch (ConnectionException $exception) {
            Log::warning('[GeminiService] Ephemeral token connection failed', ['exception' => $exception::class]);
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

    /**
     * Generate content via Google Generative Language API (Gemini).
     *
     * @param  array        $contents  Structured Gemini contents payload
     * @param  string|null  $apiKey    Optional explicit API key override
     * @param  string|null  $model     Optional explicit model override
     * @return array{text: string, usage: array{input_tokens: ?int, output_tokens: ?int, total_tokens: ?int, duration_ms: int}}
     *
     * @throws RuntimeException
     */
    public function generate(array $contents, ?string $apiKey = null, ?string $model = null): array
    {
        $resolvedKey = $this->resolveApiKey($apiKey);
        $this->ensureConfigured($resolvedKey);
        $targetModel = $model ?: config('gemini.default_model', 'gemini-3.1-flash-lite');

        $started = microtime(true);
        try {
            $response = $this->client($resolvedKey)->post(
                'v1beta/models/' . rawurlencode($targetModel) . ':generateContent',
                [
                    'systemInstruction' => ['parts' => [['text' => $this->systemInstruction()]]],
                    'contents' => $contents,
                    'generationConfig' => [
                        'maxOutputTokens' => config('gemini.max_output_tokens', 2048),
                    ],
                ]
            );

            // Fallback to gemini-flash-lite-latest if the primary model is not found
            if ($response->status() === 404 && $targetModel !== 'gemini-flash-lite-latest') {
                $response = $this->client($resolvedKey)->post(
                    'v1beta/models/gemini-flash-lite-latest:generateContent',
                    [
                        'systemInstruction' => ['parts' => [['text' => $this->systemInstruction()]]],
                        'contents' => $contents,
                        'generationConfig' => [
                            'maxOutputTokens' => config('gemini.max_output_tokens', 2048),
                        ],
                    ]
                );
            }
        } catch (ConnectionException $exception) {
            Log::warning('[GeminiService] Connection failed', ['exception' => $exception::class]);
            throw new RuntimeException('AI service is temporarily unreachable.', 0, $exception);
        }

        if (!$response->successful()) {
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

    /**
     * Live setup payload for bidirectional audio streaming.
     */
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
            'systemInstruction' => ['parts' => [['text' => $this->systemInstruction()]]],
            'inputAudioTranscription' => new \stdClass,
            'outputAudioTranscription' => new \stdClass,
            'sessionResumption' => new \stdClass,
            'contextWindowCompression' => [
                'triggerTokens' => 25000,
                'slidingWindow' => ['targetTokens' => 12000],
            ],
        ];
    }

    /**
     * Get system instruction for Gemini.
     */
    public function systemInstruction(): string
    {
        return (string) config('gemini.system_instruction');
    }

    /**
     * Check if Gemini API key is configured.
     */
    public function isConfigured(?string $apiKey = null): bool
    {
        return filled($this->resolveApiKey($apiKey));
    }

    /**
     * Resolve API key from arguments, headers, session, or config.
     */
    public function resolveApiKey(?string $apiKey = null): ?string
    {
        $requestKey = null;
        if (app()->bound('request')) {
            try {
                $req = request();
                $requestKey = $req?->header('X-Gemini-Api-Key') ?: $req?->input('gemini_api_key');
            } catch (\Throwable) {
                $requestKey = null;
            }
        }

        $sessionKey = null;
        if (app()->bound('session')) {
            try {
                $sessionKey = session('USER_GEMINI_API_KEY');
            } catch (\Throwable) {
                $sessionKey = null;
            }
        }

        $candidate = trim((string) ($apiKey ?: $requestKey ?: $sessionKey ?: config('gemini.api_key')));
        return filled($candidate) ? $candidate : null;
    }

    /**
     * Prepare HTTP client instance.
     */
    public function client(?string $apiKey = null): PendingRequest
    {
        $resolvedKey = $this->resolveApiKey($apiKey);

        return Http::baseUrl(rtrim(config('gemini.base_url', 'https://generativelanguage.googleapis.com'), '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders(['x-goog-api-key' => $resolvedKey])
            ->connectTimeout(10)
            ->timeout(config('gemini.timeout', 60))
            ->retry(2, 300, throw: false);
    }

    /**
     * Ensure the service is configured or throw an exception.
     */
    public function ensureConfigured(?string $apiKey = null): void
    {
        if (!$this->isConfigured($apiKey)) {
            throw new RuntimeException('Gemini AI service is not configured. GEMINI_API_KEY is missing.');
        }
    }

    /**
     * Map provider status codes to safe user-facing error messages.
     */
    private function friendlyProviderError(int $status, ?string $providerStatus): string
    {
        return match (true) {
            $status === 401 || $status === 403 => 'AI credentials are invalid. Please check the Gemini API key configuration.',
            $status === 429 || $providerStatus === 'RESOURCE_EXHAUSTED' => 'AI rate limit reached. Please try again in a moment.',
            $status === 404 => 'The requested Gemini AI model is currently unavailable.',
            $status >= 500 => 'Gemini AI service is temporarily unavailable.',
            default => 'AI could not complete the request. Please try again.',
        };
    }

    /**
     * Log provider failures without exposing keys or sensitive data.
     */
    private function logProviderFailure(string $operation, int $status, ?string $providerStatus): void
    {
        Log::warning('[GeminiService] Request failed', compact('operation', 'status', 'providerStatus'));
    }
}
