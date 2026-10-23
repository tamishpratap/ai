<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\AI\IraNavigationService::class);

$phrases = [
    'What pages are in earnings?',
    'Show me earnings pages',
    'Tell me about earnings',
    'What can I do in withdrawal?',
    'Can you show withdrawal section?',
    'List pages of team',
    'What is in add fund?',
    'Earning me kya hai',
    'Earning me kaunse page hai',
    'Team ke page dikhao',
    'Add fund me kya hai',
    'Withdrawal me kya kya hai'
];

foreach ($phrases as $speech) {
    $res = $service->resolveIntent($speech, 'member', '/member/dashboard');
    echo "Speech: {$speech}\n";
    echo "Intent: {$res['intent']}\n";
    echo "Section Discovery: " . (isset($res['section_discovery']) ? "YES ({$res['section_discovery']['section_name']})" : "NO") . "\n";
    echo "Speech Response: {$res['speech']}\n\n";
}
