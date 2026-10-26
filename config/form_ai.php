<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Form Filling AI Dedicated Gemini Configuration
    |--------------------------------------------------------------------------
    |
    | Completely isolated from the shared config('gemini.*') used by Navigation AI
    | and config('chat_ai.*') used by Chat AI.
    |
    | Key Resolution:
    | Reads FORM_AI_GEMINI_API_KEY first when explicitly configured;
    | falls back to CHAT_AI_GEMINI_API_KEY if absent or empty string.
    |
    */

    'api_key' => trim((string) env('FORM_AI_GEMINI_API_KEY')) !== ''
        ? env('FORM_AI_GEMINI_API_KEY')
        : env('CHAT_AI_GEMINI_API_KEY'),

    'base_url' => env('FORM_AI_GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),

    'api_version' => env('FORM_AI_GEMINI_API_VERSION', 'v1beta'),

    'default_model' => env('FORM_AI_GEMINI_MODEL', 'gemini-3.5-flash-lite'),

    'model' => env('FORM_AI_GEMINI_MODEL', 'gemini-3.5-flash-lite'),

    'timeout' => (int) env('FORM_AI_GEMINI_TIMEOUT', 3),

    'connect_timeout' => (int) env('FORM_AI_GEMINI_CONNECT_TIMEOUT', 2),

    'max_output_tokens' => (int) env('FORM_AI_GEMINI_MAX_OUTPUT_TOKENS', 500),

    'temperature' => (float) env('FORM_AI_GEMINI_TEMPERATURE', 0.1),
];
