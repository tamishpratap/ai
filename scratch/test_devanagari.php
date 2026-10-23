<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\AI\IraNavigationService::class);

$devanagariCases = [
    'ऐड फंड सेक्शन में कौन कौन से पेज हैं',
    'अर्निंग सेक्शन के बारे में बताओ',
    'टीम मैनेजमेंट में क्या क्या है',
    'विथड्रॉल सेक्शन में कौन से पेज हैं',
    'सपोर्ट सेक्शन दिखाओ',
    'पी2पी सेक्शन में कौन से पेज हैं',
    'मेंबर मैनेजमेंट के बारे में बताओ'
];

foreach ($devanagariCases as $speech) {
    $res = $service->resolveIntent($speech, 'member', '/member/dashboard');
    echo "Speech: {$speech}\n";
    echo "Intent: {$res['intent']}\n";
    echo "Speech Response: {$res['speech']}\n";
    echo "Needs Clarification: " . ($res['needs_clarification'] ? 'true' : 'false') . "\n\n";
}
