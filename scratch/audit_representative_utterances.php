<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\AI\IraNavigationService::class);

$cases = [
    ['speech' => 'Add Fund section mein kaun-kaun se pages hain?', 'role' => 'member', 'path' => '/member/dashboard'],
    ['speech' => 'Member Management ke baare mein batao.', 'role' => 'admin', 'path' => '/admin/dashboard'],
    ['speech' => 'Member Management ke baare mein batao.', 'role' => 'member', 'path' => '/member/dashboard'],
    ['speech' => 'Earning section ke andar kya-kya hai?', 'role' => 'member', 'path' => '/member/dashboard'],
    ['speech' => 'Withdrawal Section mein kaun se pages hain?', 'role' => 'member', 'path' => '/member/dashboard'],
    ['speech' => 'Reward Section ke pages batao.', 'role' => 'member', 'path' => '/member/dashboard'],
    ['speech' => 'Team Management mein kaun-kaun se pages hain?', 'role' => 'member', 'path' => '/member/dashboard'],
    ['speech' => 'Payment Management section ke andar kya hai?', 'role' => 'admin', 'path' => '/admin/dashboard'],
    ['speech' => 'P2P section mein kaun-kaun se pages hain?', 'role' => 'member', 'path' => '/member/dashboard'],
];

foreach ($cases as $c) {
    $res = $service->resolveIntent($c['speech'], $c['role'], $c['path']);
    echo "=== {$c['speech']} ({$c['role']}) ===\n";
    echo "Intent: {$res['intent']}\n";
    echo "Speech: {$res['speech']}\n";
    echo "Section Discovery: " . (isset($res['section_discovery']) ? "YES ({$res['section_discovery']['section_name']})" : "NO") . "\n";
    if (isset($res['section_discovery']['pages'])) {
        echo "Pages: " . implode(', ', $res['section_discovery']['pages']) . "\n";
    }
    echo "Context: " . json_encode($res['context'] ?? []) . "\n\n";
}
