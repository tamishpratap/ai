<?php

namespace App\Services\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FormGeminiBridge
 *
 * Dedicated, isolated Gemini AI integration service for Form Filling AI.
 * Completely decoupled from Navigation AI (GeminiService) and Chat AI (ChatGeminiBridge).
 *
 * Leverages gemini-3.5-flash-lite with bounded timeout (<=3s) for semantic slot extraction,
 * Roman Hinglish/Hindi understanding, multi-field instructions, and spoken corrections.
 */
class FormGeminiBridge
{
    /**
     * Check if Form AI has a resolved API key.
     */
    public function isConfigured(): bool
    {
        return filled($this->resolveApiKey());
    }

    /**
     * Resolve API key strictly from Form AI configuration.
     */
    public function resolveApiKey(): ?string
    {
        $key = config('form_ai.api_key');
        return filled($key) ? (string) $key : null;
    }

    /**
     * Semantic field extraction via gemini-3.5-flash-lite.
     *
     * @param  string       $speech          User speech utterance
     * @param  array        $schema          Form schema from IraFormRegistry
     * @param  array        $currentFields   Already populated field values
     * @param  string|null  $activeFieldKey  Currently focused field key in guided flow
     * @param  array        $context         Additional safe context (e.g. language)
     * @return array{
     *     success: bool,
     *     status: string,
     *     updates: array<string, mixed>,
     *     is_correction: bool,
     *     corrected_field: ?string,
     *     is_ambiguous: bool,
     *     clarification_needed: bool,
     *     clarification_prompt: ?string,
     *     duration_ms: int
     * }
     */
    public function extractFieldValues(
        string $speech,
        array $schema,
        array $currentFields = [],
        ?string $activeFieldKey = null,
        array $context = []
    ): array {
        $started = microtime(true);

        $apiKey = $this->resolveApiKey();
        if (empty($apiKey)) {
            return $this->buildResult(false, 'unconfigured', [], false, null, false, false, null, 0);
        }

        $cleanSpeech = trim($speech);
        if ($cleanSpeech === '') {
            return $this->buildResult(false, 'empty_speech', [], false, null, false, false, null, 0);
        }

        // Build filtered, non-sensitive schema payload
        $safeFields = $this->buildSafeFieldDefinitions($schema);
        if (empty($safeFields)) {
            return $this->buildResult(false, 'no_fillable_fields', [], false, null, false, false, null, 0);
        }

        $prompt = $this->buildExtractionPrompt($cleanSpeech, $schema, $safeFields, $currentFields, $activeFieldKey, $context);

        $model = config('form_ai.default_model', 'gemini-3.5-flash-lite');
        $baseUrl = rtrim(config('form_ai.base_url', 'https://generativelanguage.googleapis.com'), '/');
        $apiVersion = config('form_ai.api_version', 'v1beta');
        $timeout = (int) config('form_ai.timeout', 3);
        $connectTimeout = (int) config('form_ai.connect_timeout', 2);
        $temperature = (float) config('form_ai.temperature', 0.1);
        $maxTokens = (int) config('form_ai.max_output_tokens', 500);

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
                Log::warning('[FormGeminiBridge] API HTTP ' . $response->status() . ' error: ' . substr($response->body(), 0, 150));
                return $this->buildResult(false, 'http_error_' . $response->status(), [], false, null, false, false, null, $durationMs);
            }

            $rawJsonText = $response->json('candidates.0.content.parts.0.text');
            if (blank($rawJsonText)) {
                return $this->buildResult(false, 'empty_candidate', [], false, null, false, false, null, $durationMs);
            }

            // Strip possible markdown fences
            $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($rawJsonText));
            $parsed = json_decode($cleanJson, true);

            if (!is_array($parsed)) {
                Log::warning('[FormGeminiBridge] Malformed JSON response from Gemini');
                return $this->buildResult(false, 'malformed_json', [], false, null, false, false, null, $durationMs);
            }

            // Strictly validate proposed updates against safe registered schema
            $validUpdates = [];
            $rawUpdates = (array) ($parsed['updates'] ?? []);
            foreach ($rawUpdates as $fKey => $rawVal) {
                if (isset($safeFields[$fKey]) && $rawVal !== null && $rawVal !== '') {
                    $validUpdates[$fKey] = $this->sanitizeFieldValue($rawVal, $safeFields[$fKey]);
                }
            }

            $isCorrection = (bool) ($parsed['is_correction'] ?? false);
            $correctedField = isset($parsed['corrected_field']) && isset($safeFields[$parsed['corrected_field']])
                ? (string) $parsed['corrected_field']
                : null;
            $isAmbiguous = (bool) ($parsed['is_ambiguous'] ?? false);
            $clarificationNeeded = (bool) ($parsed['clarification_needed'] ?? false);
            $clarificationPrompt = isset($parsed['clarification_prompt']) && is_string($parsed['clarification_prompt'])
                ? trim($parsed['clarification_prompt'])
                : null;

            $status = !empty($validUpdates)
                ? 'success'
                : ($isAmbiguous || $clarificationNeeded ? 'clarification_needed' : 'no_match');

            return $this->buildResult(
                !empty($validUpdates),
                $status,
                $validUpdates,
                $isCorrection,
                $correctedField,
                $isAmbiguous,
                $clarificationNeeded,
                $clarificationPrompt,
                $durationMs
            );

        } catch (ConnectionException $e) {
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            Log::warning('[FormGeminiBridge] Connection failure / timeout: ' . $e->getMessage());
            return $this->buildResult(false, 'timeout', [], false, null, false, false, null, $durationMs);
        } catch (\Throwable $e) {
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            Log::warning('[FormGeminiBridge] Extraction exception: ' . $e->getMessage());
            return $this->buildResult(false, 'exception', [], false, null, false, false, null, $durationMs);
        }
    }

    /**
     * Filter schema to include ONLY safe, fillable, non-sensitive fields.
     */
    protected function buildSafeFieldDefinitions(array $schema): array
    {
        $safe = [];
        $fields = $schema['fields'] ?? [];

        foreach ($fields as $key => $def) {
            // Exclude sensitive fields (passwords, PINs, OTPs, CAPTCHAs)
            if ($def['sensitive'] ?? false) {
                continue;
            }
            // Exclude file uploads
            if ($def['file_upload'] ?? false) {
                continue;
            }
            // Exclude read-only or hidden fields
            if ($def['read_only'] ?? false) {
                continue;
            }

            $safe[$key] = [
                'name' => $def['name'] ?? $key,
                'label' => $def['label'] ?? $key,
                'type' => $def['type'] ?? 'text',
                'aliases' => array_slice((array) ($def['aliases'] ?? []), 0, 8),
                'options' => $this->sanitizeOptions($def['options'] ?? []),
            ];
        }

        return $safe;
    }

    /**
     * Sanitize select / radio options for prompt context.
     */
    protected function sanitizeOptions(array $options): array
    {
        $clean = [];
        foreach ($options as $opt) {
            if (isset($opt['value'])) {
                $clean[] = [
                    'value' => (string) $opt['value'],
                    'label' => (string) ($opt['label'] ?? $opt['value']),
                ];
            }
        }
        return array_slice($clean, 0, 15);
    }

    /**
     * Build structured prompt for Gemini extraction.
     */
    protected function buildExtractionPrompt(
        string $speech,
        array $schema,
        array $safeFields,
        array $currentFields,
        ?string $activeFieldKey,
        array $context
    ): string {
        $fieldsJson = json_encode($safeFields, JSON_UNESCAPED_UNICODE);

        // Sanitize currentFields so no secrets or unnecessary data leak
        $safeCurrent = array_intersect_key($currentFields, $safeFields);
        $currentJson = json_encode($safeCurrent, JSON_UNESCAPED_UNICODE);

        $activeNotice = $activeFieldKey && isset($safeFields[$activeFieldKey])
            ? "The currently active/focused field is '{$activeFieldKey}' ({$safeFields[$activeFieldKey]['label']}). Prioritize resolving this field unless the user explicitly names or gives values for other fields."
            : "No field is currently focused; extract any mentioned fields.";

        return <<<PROMPT
You are an intelligent voice form-filling AI for the form '{$schema['title']}'.
The user communicates in English, Hindi (Devanagari), or conversational Roman Hinglish (e.g., "500 dollar transfer karo", "mera naam Ramesh Kumar likh do", "gold package select karo", "nahi 1000 nahi 2000").

Registered Non-Sensitive Fields:
{$fieldsJson}

Currently Filled Fields:
{$currentJson}

{$activeNotice}

User Utterance:
"{$speech}"

Instructions:
1. Extract values the user wants to fill into the registered fields.
2. For numbers: extract pure numeric values (e.g., "paanch sau" -> 500, "1,000" -> 1000, "२५००" -> 2500).
3. For select/radio fields: match against canonical option 'value' or 'label'.
4. For multi-field sentences: extract values for all mentioned fields (e.g., "GAB10045 ko 500 bhejo" -> memberid: "GAB10045", amount: 500).
5. For corrections (e.g., "1000 nahi 2000", "500 ki jagah 700", "change amount to 2000"): set 'is_correction': true and extract the NEW corrected value.
6. If the user instruction is ambiguous, unclear, or asks an unrelated question: set 'is_ambiguous': true or 'clarification_needed': true.
7. NEVER invent field keys. ONLY use keys from Registered Non-Sensitive Fields.
8. NEVER extract passwords, PINs, OTPs, or credit card numbers.

Return strictly a valid JSON object matching this schema:
{
  "updates": { "field_key": "extracted_value" },
  "is_correction": false,
  "corrected_field": null,
  "is_ambiguous": false,
  "clarification_needed": false,
  "clarification_prompt": null
}
PROMPT;
    }

    /**
     * Sanitize and cast extracted value based on field type.
     */
    protected function sanitizeFieldValue(mixed $val, array $fieldDef): mixed
    {
        $type = $fieldDef['type'] ?? 'text';

        if ($type === 'number') {
            if (is_numeric($val)) {
                return str_contains((string) $val, '.') ? (float) $val : (int) $val;
            }
            $clean = preg_replace('/[^\d.]/', '', (string) $val);
            if (is_numeric($clean)) {
                return str_contains($clean, '.') ? (float) $clean : (int) $clean;
            }
        }

        if ($type === 'checkbox') {
            if (is_bool($val)) return $val;
            $s = strtolower(trim((string) $val));
            return in_array($s, ['true', '1', 'yes', 'on', 'checked']);
        }

        if ($type === 'select' || $type === 'radio') {
            // Canonicalize to matching option value if available
            $options = $fieldDef['options'] ?? [];
            foreach ($options as $opt) {
                if (strcasecmp((string) $opt['value'], (string) $val) === 0 ||
                    strcasecmp((string) $opt['label'], (string) $val) === 0) {
                    return $opt['value'];
                }
            }
        }

        return is_scalar($val) ? trim((string) $val) : $val;
    }

    /**
     * Helper to construct typed response array.
     */
    protected function buildResult(
        bool $success,
        string $status,
        array $updates,
        bool $isCorrection,
        ?string $correctedField,
        bool $isAmbiguous,
        bool $clarificationNeeded,
        ?string $clarificationPrompt,
        int $durationMs
    ): array {
        return [
            'success' => $success,
            'status' => $status,
            'updates' => $updates,
            'is_correction' => $isCorrection,
            'corrected_field' => $correctedField,
            'is_ambiguous' => $isAmbiguous,
            'clarification_needed' => $clarificationNeeded,
            'clarification_prompt' => $clarificationPrompt,
            'duration_ms' => $durationMs,
        ];
    }
}
