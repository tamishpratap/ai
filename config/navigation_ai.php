<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Navigation AI Dedicated Gemini Configuration
    |--------------------------------------------------------------------------
    |
    | Isolated configuration for IRA Voice Navigation AI. Completely decoupled
    | from config('gemini.*') and config('chat_ai.*').
    |
    | Key Resolution:
    | Reads NAVIGATION_AI_GEMINI_API_KEY first when explicitly configured;
    | falls back to CHAT_AI_GEMINI_API_KEY if absent or empty string.
    |
    */

    'api_key' => trim((string) env('NAVIGATION_AI_GEMINI_API_KEY')) !== ''
        ? env('NAVIGATION_AI_GEMINI_API_KEY')
        : env('CHAT_AI_GEMINI_API_KEY'),

    'base_url' => env('NAVIGATION_AI_GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),

    'api_version' => env('NAVIGATION_AI_GEMINI_API_VERSION', 'v1beta'),

    'default_model' => env('NAVIGATION_AI_GEMINI_MODEL', 'gemini-3.5-flash-lite'),

    'model' => env('NAVIGATION_AI_GEMINI_MODEL', 'gemini-3.5-flash-lite'),

    'timeout' => (float) env('NAVIGATION_AI_GEMINI_TIMEOUT', 3.5),

    'connect_timeout' => (float) env('NAVIGATION_AI_GEMINI_CONNECT_TIMEOUT', 2.0),

    'max_output_tokens' => (int) env('NAVIGATION_AI_GEMINI_MAX_OUTPUT_TOKENS', 350),

    'temperature' => (float) env('NAVIGATION_AI_GEMINI_TEMPERATURE', 0.1),
];
