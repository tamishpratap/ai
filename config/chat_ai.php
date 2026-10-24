<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chat AI Dedicated Gemini Configuration
    |--------------------------------------------------------------------------
    |
    | Completely isolated from the shared GEMINI_API_KEY / config('gemini.*')
    | used by Navigation AI and Form Filling AI.
    |
    */

    'api_key' => env('CHAT_AI_GEMINI_API_KEY'),

    'base_url' => env('CHAT_AI_GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),

    'api_version' => env('CHAT_AI_GEMINI_API_VERSION', 'v1beta'),

    'default_model' => env('CHAT_AI_GEMINI_MODEL', 'gemini-3.5-flash-lite'),

    'model' => env('CHAT_AI_GEMINI_MODEL', 'gemini-3.5-flash-lite'),

    'timeout' => (int) env('CHAT_AI_GEMINI_TIMEOUT', 5),

    'max_output_tokens' => (int) env('CHAT_AI_GEMINI_MAX_OUTPUT_TOKENS', 450),

    'temperature' => (float) env('CHAT_AI_GEMINI_TEMPERATURE', 0.25),
];
