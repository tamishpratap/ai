<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\AppNavigationOntology;

echo "Ontology Section Counts:" . PHP_EOL;
echo "Member Sections: " . count(AppNavigationOntology::getSectionsForPanel('member')) . PHP_EOL;
echo "Admin Sections: " . count(AppNavigationOntology::getSectionsForPanel('admin')) . PHP_EOL;
echo "Total Destinations: " . count(AppNavigationOntology::getAllDestinations()) . PHP_EOL;

// Test findSectionByAlias with Devanagari and Latin
$testCases = [
    ['अर्निंग सेक्शन में कौन-कौन से पेज हैं', 'member', 'earnings'],
    ['ऐड फंड सेक्शन में कौन से पेज हैं', 'member', 'add_fund'],
    ['विड्रॉल सेक्शन में कौन से पेज हैं', 'member', 'withdrawal_section'],
    ['रिवॉर्ड सेक्शन के पेज बताओ', 'member', 'reward_section'],
    ['टीम मैनेजमेंट में कौन-कौन से पेज हैं', 'member', 'team_management'],
    ['पी2पी सेक्शन में कौन से पेज हैं', 'member', 'p2p'],
    ['Add Fund section mein kaun-kaun se pages hain?', 'member', 'add_fund'],
    ['Member Management ke baare mein batao.', 'admin', 'member_management'],
    ['Payment Management section ke andar kya hai?', 'admin', 'payment_management'],
    ['Show me all pages in the Income section', 'member', 'earnings'],
    ['Earning me kaunse page hai', 'member', 'earnings'],
];

foreach ($testCases as [$utterance, $panel, $expectedKey]) {
    $match = AppNavigationOntology::findSectionByAlias($utterance, $panel);
    $matchedKey = $match['key'] ?? 'NONE';
    $status = ($matchedKey === $expectedKey) ? 'PASS' : 'FAIL';
    echo "[$status] '$utterance' ($panel) -> Expected: $expectedKey, Got: $matchedKey" . PHP_EOL;
}
