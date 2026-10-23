<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\AppNavigationOntology;

$testPages = [
    ['Geneology kholo', 'member', 'member_team_geneology'],
    ['Direct Income dikhao', 'member', 'member_income_direct'],
    ['Add Fund History par le chalo', 'member', 'member_fund_history'],
    ['Members Details kholo', 'admin', 'admin_member_details'],
    ['जीनोलॉजी खोलो', 'member', 'member_team_geneology'],
    ['डायरेक्ट इनकम', 'member', 'member_income_direct'],
    ['ऐड फंड हिस्ट्री', 'member', 'member_fund_history'],
    ['मेंबर्स डिटेल्स', 'admin', 'admin_member_details'],
];

foreach ($testPages as [$utterance, $panel, $expectedKey]) {
    $match = AppNavigationOntology::findPageByAlias($utterance, $panel);
    $matchedKey = $match['key'] ?? 'NONE';
    $status = ($matchedKey === $expectedKey) ? 'PASS' : 'FAIL';
    echo "[$status] '$utterance' ($panel) -> Expected: $expectedKey, Got: $matchedKey" . PHP_EOL;
}
