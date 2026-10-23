<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\IraNavigationService;
use App\Services\Gemini\GeminiService;

$gemini = new GeminiService();
$service = new IraNavigationService($gemini);

// Test 1: Devanagari Member section inquiries
$devanagariTests = [
    'अर्निंग सेक्शन में कौन-कौन से पेज हैं' => ['intent' => 'clarify', 'sec' => 'earnings'],
    'ऐड फंड सेक्शन में कौन से पेज हैं' => ['intent' => 'clarify', 'sec' => 'add_fund'],
    'विड्रॉल सेक्शन में कौन से पेज हैं' => ['intent' => 'clarify', 'sec' => 'withdrawal_section'],
    'रिवॉर्ड सेक्शन के पेज बताओ' => ['intent' => 'clarify', 'sec' => 'reward_section'],
    'टीम मैनेजमेंट में कौन-कौन से पेज हैं' => ['intent' => 'clarify', 'sec' => 'team_management'],
    'पी2पी सेक्शन में कौन से पेज हैं' => ['intent' => 'clarify', 'sec' => 'p2p'],
    'मुझे अर्निंग सेक्शन के पेज बताओ।' => ['intent' => 'clarify', 'sec' => 'earnings'],
];

echo "--- RUNNING DEVANAGARI SECTION DISCOVERY TESTS ---" . PHP_EOL;
foreach ($devanagariTests as $speech => $expected) {
    $res = $service->resolveIntent($speech, 'member', '/member/dashboard', 'chat', []);
    $matchedSec = $res['section_discovery']['section_key'] ?? 'NONE';
    $intent = $res['intent'] ?? 'NONE';
    $status = ($intent === $expected['intent'] && $matchedSec === $expected['sec']) ? 'PASS' : 'FAIL';
    echo "[$status] '$speech' -> Intent: $intent, Section: $matchedSec" . PHP_EOL;
    if ($status === 'FAIL') {
        echo "   Response speech: " . ($res['speech'] ?? '') . PHP_EOL;
    }
}

// Test 2: Latin Representative Utterances from Audit
$latinTests = [
    'Add Fund section mein kaun-kaun se pages hain?' => ['role' => 'member', 'intent' => 'clarify', 'sec' => 'add_fund'],
    'Member Management ke baare mein batao.' => ['role' => 'member', 'intent' => 'unauthorized'],
    'Earning section ke andar kya-kya hai?' => ['role' => 'member', 'intent' => 'clarify', 'sec' => 'earnings'],
    'Withdrawal Section mein kaun se pages hain?' => ['role' => 'member', 'intent' => 'clarify', 'sec' => 'withdrawal_section'],
    'Reward Section ke pages batao.' => ['role' => 'member', 'intent' => 'clarify', 'sec' => 'reward_section'],
    'Team Management mein kaun-kaun se pages hain?' => ['role' => 'member', 'intent' => 'clarify', 'sec' => 'team_management'],
    'Payment Management section ke andar kya hai?' => ['role' => 'member', 'intent' => 'unauthorized'],
    'P2P section mein kaun-kaun se pages hain?' => ['role' => 'member', 'intent' => 'clarify', 'sec' => 'p2p'],
    'Show me all pages in the Income section' => ['role' => 'member', 'intent' => 'clarify', 'sec' => 'earnings'],
    'Earning me kaunse page hai' => ['role' => 'member', 'intent' => 'clarify', 'sec' => 'earnings'],
];

echo PHP_EOL . "--- RUNNING LATIN REPRESENTATIVE UTTERANCES ---" . PHP_EOL;
foreach ($latinTests as $speech => $expected) {
    $role = $expected['role'];
    $res = $service->resolveIntent($speech, $role, '/member/dashboard', 'chat', []);
    $matchedSec = $res['section_discovery']['section_key'] ?? 'NONE';
    $intent = $res['intent'] ?? 'NONE';
    $expectedSec = $expected['sec'] ?? 'NONE';
    
    $pass = ($intent === $expected['intent']) && ($expectedSec === 'NONE' || $matchedSec === $expectedSec);
    $status = $pass ? 'PASS' : 'FAIL';
    echo "[$status] '$speech' ($role) -> Intent: $intent, Sec: $matchedSec" . PHP_EOL;
    if (!$pass) {
        echo "   Response speech: " . ($res['speech'] ?? '') . PHP_EOL;
    }
}
