<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$navService = app(App\Services\AI\IraNavigationService::class);

echo "--- Test 12 Debug ---\n";
$res12 = $navService->resolveIntent("Member panel ke Team section mein kaun-kaun se pages hain?", 'member', '/member/dashboard');
echo "Intent: " . $res12['intent'] . "\n";
echo "Speech: " . ($res12['speech'] ?? '') . "\n";
print_r($res12['context'] ?? []);

echo "\n--- Test 16 Debug ---\n";
$prompt = $navService->resolveIntent("Mujhe logout karna hai", 'member', '/member/dashboard');
echo "Prompt intent: " . $prompt['intent'] . "\n";
print_r($prompt['context']);

$cancelled = $navService->resolveIntent("Nahi, cancel karo", 'member', '/member/dashboard', 'chat', $prompt['context']);
echo "Cancelled intent: " . $cancelled['intent'] . "\n";
echo "Cancelled speech: " . ($cancelled['speech'] ?? '') . "\n";
