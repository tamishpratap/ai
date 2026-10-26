<?php

namespace App\Services\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * NavigationGeminiBridge
 *
 * Dedicated, isolated Gemini AI integration service for IRA Voice Navigation AI.
 * Decoupled from Form Filling AI (FormGeminiBridge), Chat AI (ChatGeminiBridge),
 * and shared legacy Gemini infrastructure (GeminiService).
 *
 * Utilizes gemini-3.5-flash-lite over v1beta endpoint with bounded timeouts
 * for semantic intent understanding, Hindi/Roman Hinglish/English translation,
 * phonetic speech correction, and contextual multi-turn follow-ups.
 */
class NavigationGeminiBridge
{
    /**
     * Check if Navigation AI has a resolved API key.
     */
    public function isConfigured(): bool
    {
        return filled($this->resolveApiKey());
    }

    /**
     * Resolve API key strictly according to key precedence:
     * 1. NAVIGATION_AI_GEMINI_API_KEY
     * 2. CHAT_AI_GEMINI_API_KEY (fallback)
     */
    public function resolveApiKey(): ?string
    {
        $key = config('navigation_ai.api_key');
        return filled($key) ? trim((string) $key) : null;
    }

    /**
     * Resolve user navigation intent using gemini-3.5-flash-lite.
     *
     * @param  string  $speech       Raw or sanitized user utterance
     * @param  string  $role         Server-authoritative role: 'member', 'admin', or 'guest'
     * @param  string  $currentPage  Current page path or route name
     * @param  string  $lang         Detected utterance language ('hinglish', 'hindi', 'english')
     * @param  array   $context      Session navigation context (pending options, clarification state)
     * @return array{
     *     success: bool,
     *     status: string,
     *     intent: string,
     *     destination_key: ?string,
     *     confidence: float,
     *     speech: ?string,
     *     response_language: string,
     *     options: array,
     *     duration_ms: int
     * }
     */
    public function resolveIntent(
        string $speech,
        string $role,
        string $currentPage = '',
        string $lang = 'hinglish',
        array $context = []
    ): array {
        $started = microtime(true);

        $apiKey = $this->resolveApiKey();
        if (empty($apiKey)) {
            return $this->buildFailureResult('unconfigured', 'unsupported_action', null, 0.0, 'AI navigation key is not configured.', $lang, [], 0);
        }

        $cleanSpeech = trim($speech);
        if ($cleanSpeech === '') {
            return $this->buildFailureResult('empty_speech', 'unsupported_action', null, 0.0, 'Please state where you would like to go.', $lang, [], 0);
        }

        // Fetch allowed destinations strictly from the authoritative navigation ontology
        $allowedDestinations = $this->getAllowedDestinationsForRole($role);
        $allDestinations = AppNavigationOntology::getAllDestinations();

        $prompt = $this->buildNavigationPrompt($cleanSpeech, $role, $currentPage, $lang, $context, $allowedDestinations);

        $model = config('navigation_ai.default_model', 'gemini-3.5-flash-lite');
        $baseUrl = rtrim(config('navigation_ai.base_url', 'https://generativelanguage.googleapis.com'), '/');
        $apiVersion = config('navigation_ai.api_version', 'v1beta');
        $timeout = (float) config('navigation_ai.timeout', 3.5);
        $connectTimeout = (float) config('navigation_ai.connect_timeout', 2.0);
        $temperature = (float) config('navigation_ai.temperature', 0.1);
        $maxTokens = (int) config('navigation_ai.max_output_tokens', 350);

        $endpoint = "{$baseUrl}/{$apiVersion}/models/{$model}:generateContent";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey,
            ])
            ->connectTimeout($connectTimeout)
            ->timeout($timeout)
            ->post($endpoint, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => $maxTokens,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            $durationMs = (int) round((microtime(true) - $started) * 1000);

            if (!$response->successful()) {
                Log::warning('[NavigationGeminiBridge] HTTP ' . $response->status() . ' error: ' . substr($response->body(), 0, 150));
                return $this->buildFailureResult('http_error', 'unsupported_action', null, 0.0, null, $lang, [], $durationMs);
            }

            $body = $response->json();
            $rawText = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $rawText = trim($rawText);

            if ($rawText === '') {
                return $this->buildFailureResult('empty_response', 'unsupported_action', null, 0.0, null, $lang, [], $durationMs);
            }

            // Strip Markdown JSON code fences if returned by model
            $cleanJson = preg_replace('/^```(?:json)?\s*/iu', '', $rawText);
            $cleanJson = preg_replace('/\s*```$/u', '', (string) $cleanJson);
            $cleanJson = trim((string) $cleanJson);

            $parsed = json_decode($cleanJson, true);
            if (!is_array($parsed) || empty($parsed['intent'])) {
                Log::warning('[NavigationGeminiBridge] Malformed JSON payload: ' . substr($cleanJson, 0, 120));
                return $this->buildFailureResult('malformed_json', 'unsupported_action', null, 0.0, null, $lang, [], $durationMs);
            }

            $intent = (string) $parsed['intent'];
            $destKey = !empty($parsed['destination_key']) ? trim((string) $parsed['destination_key']) : null;
            $confidence = isset($parsed['confidence']) ? (float) $parsed['confidence'] : 0.85;
            $speechResponse = !empty($parsed['speech']) ? trim((string) $parsed['speech']) : null;
            $respLang = !empty($parsed['response_language']) ? trim((string) $parsed['response_language']) : $lang;
            $options = is_array($parsed['options'] ?? null) ? $parsed['options'] : [];

            // 1. Core Window Controls / Browser History intents
            if (in_array($intent, ['go_back', 'close_assistant', 'go_forward'], true)) {
                return [
                    'success' => true,
                    'status' => 'resolved',
                    'intent' => $intent,
                    'destination_key' => null,
                    'confidence' => $confidence,
                    'speech' => $speechResponse,
                    'response_language' => $respLang,
                    'options' => [],
                    'duration_ms' => $durationMs,
                ];
            }

            // 2. Clarification intent
            if ($intent === 'clarify') {
                // Filter proposed options so only permitted destinations for this role remain
                $filteredOptions = [];
                foreach ($options as $optKey) {
                    if (isset($allowedDestinations[$optKey])) {
                        $filteredOptions[] = $optKey;
                    }
                }

                return [
                    'success' => true,
                    'status' => 'clarify',
                    'intent' => 'clarify',
                    'destination_key' => null,
                    'confidence' => $confidence,
                    'speech' => $speechResponse,
                    'response_language' => $respLang,
                    'options' => $filteredOptions,
                    'duration_ms' => $durationMs,
                ];
            }

            // 3. Unauthorized intent (e.g. member requesting admin section)
            if ($intent === 'unauthorized') {
                return [
                    'success' => false,
                    'status' => 'unauthorized',
                    'intent' => 'unauthorized',
                    'destination_key' => null,
                    'confidence' => max($confidence, 0.95),
                    'speech' => $speechResponse ?: ($respLang === 'english'
                        ? "You do not have permission to access that section with your current account."
                        : "Yeh section aapke account role ke liye permitted nahi hai."),
                    'response_language' => $respLang,
                    'options' => [],
                    'duration_ms' => $durationMs,
                ];
            }

            // 4. Navigation intent - STRICT SERVER VALIDATION
            if ($intent === 'navigate') {
                if ($destKey && isset($allowedDestinations[$destKey])) {
                    return [
                        'success' => true,
                        'status' => 'resolved',
                        'intent' => 'navigate',
                        'destination_key' => $destKey,
                        'confidence' => $confidence,
                        'speech' => $speechResponse,
                        'response_language' => $respLang,
                        'options' => [],
                        'duration_ms' => $durationMs,
                    ];
                }

                // If destination exists in app ontology but is NOT allowed for this role -> strict unauthorized!
                if ($destKey && isset($allDestinations[$destKey])) {
                    Log::warning("[NavigationGeminiBridge] Cross-role destination rejected: role={$role}, requested={$destKey}");
                    return [
                        'success' => false,
                        'status' => 'unauthorized',
                        'intent' => 'unauthorized',
                        'destination_key' => null,
                        'confidence' => 0.98,
                        'speech' => $respLang === 'english'
                            ? "You do not have permission to access that section."
                            : "Yeh section aapke account role ke liye permitted nahi hai.",
                        'response_language' => $respLang,
                        'options' => [],
                        'duration_ms' => $durationMs,
                    ];
                }

                // Hallucinated destination key or invalid URL
                Log::warning("[NavigationGeminiBridge] Rejected unrecognized destination key: {$destKey}");
                return $this->buildFailureResult('invalid_destination', 'unsupported_action', null, 0.5, null, $respLang, [], $durationMs);
            }

            // 5. Unsupported action / other
            return $this->buildFailureResult('unsupported', 'unsupported_action', null, $confidence, $speechResponse, $respLang, [], $durationMs);

        } catch (ConnectionException $e) {
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            Log::warning('[NavigationGeminiBridge] Connection timeout or failure after ' . $durationMs . 'ms: ' . $e->getMessage());
            return $this->buildFailureResult('timeout', 'unsupported_action', null, 0.0, null, $lang, [], $durationMs);
        } catch (\Throwable $e) {
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            Log::warning('[NavigationGeminiBridge] Exception: ' . $e->getMessage());
            return $this->buildFailureResult('exception', 'unsupported_action', null, 0.0, null, $lang, [], $durationMs);
        }
    }

    /**
     * Build allowed destinations summary for the prompt.
     */
    public function getAllowedDestinationsForRole(string $role): array
    {
        $all = AppNavigationOntology::getAllDestinations();
        $allowed = [];
        foreach ($all as $key => $meta) {
            if (in_array($role, $meta['roles'] ?? [], true)) {
                $allowed[$key] = $meta;
            }
        }
        return $allowed;
    }

    /**
     * Build compact, role-scoped prompt for Gemini 3.5 Flash-Lite.
     */
    public function buildNavigationPrompt(
        string $speech,
        string $role,
        string $currentPage,
        string $lang,
        array $context,
        array $allowedDestinations
    ): string {
        $destinationsSummary = [];
        foreach ($allowedDestinations as $key => $meta) {
            $destinationsSummary[] = [
                'key' => $key,
                'label' => $meta['label'] ?? '',
                'section' => $meta['section'] ?? '',
                'sample_aliases' => array_slice($meta['aliases'] ?? [], 0, 4),
            ];
        }

        $destinationsJson = json_encode($destinationsSummary, JSON_UNESCAPED_UNICODE);
        $contextJson = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '{}';

        return <<<PROMPT
You are IRA, the voice navigation assistant for Quantum ARB.
Understand natural spoken commands in Hindi, everyday Roman Hinglish, and Indian English.
Tolerate speech-recognition misspellings (e.g. 'dashbord' -> dashboard, 'profiel' -> profile, 'withdrawl' -> withdrawal), colloquial phrasing ('mera profile open karo', 'P2P open karna hai', 'earning section dikhao'), and follow-ups.

Current User State:
- Server-Verified Role: "{$role}"
- Current Page: "{$currentPage}"
- Detected Language: "{$lang}"
- Active Context: {$contextJson}

PERMITTED DESTINATIONS FOR THIS ROLE:
{$destinationsJson}

CRITICAL RULES:
1. Intent Mapping:
   - "navigate": ONLY when the user wants to go to one of the PERMITTED DESTINATIONS above. "destination_key" MUST be the exact 'key' from the list.
   - "clarify": When the request matches multiple possible destinations (e.g. 'investment' could be member_staking or member_staking_details), or is ambiguous. List candidate keys in "options".
   - "unauthorized": If role is 'member' or 'guest' and user asks for an Administrator section (e.g. Admin Dashboard, Member Management, Account Control, System Settings). Set destination_key to null.
   - "go_back": If user asks to go to previous page / wapas jao / back.
   - "close_assistant": If user asks to close or dismiss assistant window.
   - "unsupported_action": If request is not a navigation query.

2. SPECIFIC PHRASE FIXES:
   - "member dashboard kholo", "member dashbord kholo", "dashboard par chalo" by a Member -> destination_key: "member_dashboard" (DO NOT treat this as admin member management!).
   - "profiel pe le chalo", "mera profile open karo" -> destination_key: "member_profile_summary" (or "admin_profile" if role is admin).
   - "withdrawl page kholo", "withdrawal open karo" -> destination_key: "member_wallet_withdraw" (or "member_wallet_history" if specified).
   - "P2P open karna hai" -> destination_key: "member_p2p_wallet".

3. OUTPUT FORMAT:
Return strictly a valid JSON object with NO surrounding markdown or extra commentary:
{
  "intent": "navigate" | "clarify" | "go_back" | "close_assistant" | "unauthorized" | "unsupported_action",
  "destination_key": "canonical_key_string" | null,
  "confidence": 0.0 to 1.0,
  "speech": "Short, polite conversational response in natural Hindi/Hinglish/English matching user utterance",
  "response_language": "hinglish" | "hindi" | "english",
  "options": ["key1", "key2"]
}

User Utterance: "{$speech}"
PROMPT;
    }

    /**
     * Helper to construct a standardized failure result array.
     */
    protected function buildFailureResult(
        string $status,
        string $intent,
        ?string $destinationKey,
        float $confidence,
        ?string $speech,
        string $lang,
        array $options,
        int $durationMs
    ): array {
        return [
            'success' => false,
            'status' => $status,
            'intent' => $intent,
            'destination_key' => $destinationKey,
            'confidence' => $confidence,
            'speech' => $speech,
            'response_language' => $lang,
            'options' => $options,
            'duration_ms' => $durationMs,
        ];
    }
}
