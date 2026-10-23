<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$navService = app(App\Services\AI\IraNavigationService::class);

$p = "Earning section mein kaun-kaun se pages hain?";
$res = $navService->resolveIntent($p, 'member', '/member/dashboard');
echo "Query: $p\n";
echo "Intent: " . $res['intent'] . "\n";
echo "Speech: " . $res['speech'] . "\n";
print_r($res['context'] ?? []);
