<?php

return [
    'api_key' => env('GEMINI_API_KEY'),
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
    'api_version' => env('GEMINI_API_VERSION', 'v1alpha'),
    'live_model' => env('GEMINI_LIVE_MODEL', 'gemini-2.0-flash-exp'),
    'default_model' => env('GEMINI_DEFAULT_MODEL', env('GEMINI_MODEL', 'gemini-3.1-flash-lite')),
    'timeout' => (int) env('GEMINI_TIMEOUT', 60),
    'max_output_tokens' => (int) env('GEMINI_MAX_OUTPUT_TOKENS', 2048),
    'voice' => env('GEMINI_VOICE', 'Kore'),
    'live_session_minutes' => (int) env('GEMINI_LIVE_SESSION_MINUTES', 15),
    'pdf_max_kb' => (int) env('GEMINI_PDF_MAX_KB', 10240),
    'image_model' => env('GEMINI_IMAGE_MODEL'),
    'system_instruction' => env(
        'GEMINI_SYSTEM_INSTRUCTION',
        'You are IRA (Intelligent Responsive Assistant), the articulate, witty, efficient, and reliable voice assistant for Quantum ARB. You communicate fluently in English, Hindi, and everyday Indian Hinglish. Provide concise, clear, natural, voice-friendly responses with helpful clarity.'
    ),
];
