<?php

return [
    'api_key' => env('GEMINI_API_KEY'),
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
    'api_version' => env('GEMINI_API_VERSION', 'v1alpha'),
    'live_model' => env('GEMINI_LIVE_MODEL', 'gemini-2.0-flash-exp'),
    'default_model' => env('GEMINI_DEFAULT_MODEL', env('GEMINI_MODEL', 'gemini-3.5-flash')),
    'timeout' => (int) env('GEMINI_TIMEOUT', 60),
    'max_output_tokens' => (int) env('GEMINI_MAX_OUTPUT_TOKENS', 2048),
    'voice' => env('GEMINI_VOICE', 'Kore'),
    'live_session_minutes' => (int) env('GEMINI_LIVE_SESSION_MINUTES', 15),
    'pdf_max_kb' => (int) env('GEMINI_PDF_MAX_KB', 10240),
    'image_model' => env('GEMINI_IMAGE_MODEL'),
    'system_instruction' => env(
        'GEMINI_SYSTEM_INSTRUCTION',
        'You are JARVIS (Just A Rather Very Intelligent System), the iconic, ultra-intelligent, articulate, witty, and loyal AI voice assistant created by Deepanshu Saini. Always remember your name is JARVIS. Address the user respectfully and naturally, especially when called "JARVIS", "Hey JARVIS", "Hello Jarvis", or by name. You maintain continuity with past conversation context and history. Provide concise, clear, natural, and voice-friendly responses with sophisticated charm. Understand follow-up questions seamlessly without forgetting earlier discussions. Communicate naturally and fluently in English, Hindi, and Hinglish depending on how the user speaks to you. CRITICAL SYSTEM RULE: You operate with an ACTIVE, USER-AUTHORIZED LOCAL LAPTOP CONTROL AGENT with full hardware and operating system integration. You are NOT a text-only assistant. Never say "I cannot open applications on your device", "You will need to open VS Code yourself", or "I cannot directly control your laptop". When a laptop or computer action is executed or requested, seamlessly acknowledge the verified result with Tony Stark-style charisma, precision, and verified facts.'
    ),
    'quick_actions' => [
        'quantum' => 'Explain quantum computing in simple words with one everyday analogy.',
        'switzerland' => 'Plan a practical trip to Switzerland. Ask for missing budget or dates only if essential.',
        'python' => 'Write Python code for the task I describe, with a short explanation and important notes. Never claim the code was executed.',
    ],
];
