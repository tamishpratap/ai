<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\IraNavigationService;

$service = app(IraNavigationService::class);

$checks = [
    ['Earning section mein kaun se pages hain?', 'member', 'clarify', 'section_page_choice'],
    ['P2P section mein kya hai?', 'member', 'clarify', 'section_page_choice'],
    ['Reward section mein kaun se pages hain?', 'member', 'clarify', 'section_page_choice'],
    ['Go forward', 'member', 'go_forward', null],
    ['Next page', 'member', 'go_forward', null],
    ['Go back', 'member', 'go_back', null],
    ['Dismiss', 'member', 'close_assistant', null],
    ['Close assistant', 'member', 'close_assistant', null],
    ['Trading kholo', 'member', 'navigate', null],
];

$allPass = true;
foreach ($checks as $c) {
    $res = $service->resolveIntent($c[0], $c[1], '/member/dashboard');
    $intentOk = ($res['intent'] === $c[2]);
    $ctxOk = $c[3] === null || (($res['context']['pending_clarification'] ?? '') === $c[3]);
    $pass = $intentOk && $ctxOk;
    if (!$pass) $allPass = false;
    echo ($pass ? '✅ PASS: ' : '❌ FAIL: ') . $c[0] . ' -> ' . $res['intent'] . PHP_EOL;
}

exit($allPass ? 0 : 1);
