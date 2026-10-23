<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\AppNavigationOntology;

function testResolveSectionDiscovery(string $raw, string $role, string $lang = 'hinglish', string $currentPage = '')
{
    $panelKey = ($role === 'admin') ? 'admin' : 'member';
    $otherPanelKey = ($role === 'admin') ? 'member' : 'admin';

    $clean = mb_strtolower(trim($raw), 'UTF-8');

    // 1. Contextual "is section ke pages"
    $isContextual = preg_match('/\b(?:is|iss|this)\s+section\b/iu', $clean)
        || preg_match('/(?:इस\s*सेक्शन|इस\s*सेक्शन\s*के)/u', $raw);

    // 2. Comprehensive pattern for questions inquiring about section pages, contents, or details
    $isSectionQuestion = preg_match('/\b(?:'
        . 'ke\s+baare\s+mein\s+batao|ke\s+bare\s+me\s+batao|ke\s+baare\s+mein|ke\s+bare\s+me|baare\s+mein|bare\s+me|'
        . 'batao|bataiye|samjhao|explain|tell\s+me\s+about|about|'
        . 'ke\s+andar\s+kya[-\s]*kya\s+hai|ke\s+andar\s+kya\s+hai|ke\s+andar|ke\s+under|andar\s+kya\s+hai|'
        . 'kya[-\s]*kya\s+pages\s+hain|kya\s+pages\s+hain|kya[-\s]*kya\s+available\s+hai|kya\s+available\s+hai|kya[-\s]*kya\s+hai|kya\s+hai|'
        . 'mein\s+kaun[-\s]*(?:kaun\s*)?se\s+pages?|kaun[-\s]*(?:kaun\s*)?se\s+pages?|kaunse\s+pages?|kaun\s+se\s+pages?|'
        . 'kitne\s+pages?\s+hain|kitne\s+pages?|kaun[-\s]*kaun\s+se\s+options|'
        . 'ke\s+pages?\s+batao|ke\s+pages?\s+dikhao|ke\s+saare\s+pages?|ke\s+all\s+pages?|pages?\s+batao|pages?\s+dikhao|pages?\s+list|'
        . 'what\s+pages\s+are\s+(?:in|under)|which\s+pages\s+are\s+(?:in|under)|what\s+is\s+(?:in|under|inside)|'
        . 'list\s+(?:all\s+)?pages\s+(?:in|under|of)|show\s+(?:all\s+)?pages\s+(?:in|under|of)|show\s+me\s+(?:all\s+)?(?:the\s+)?pages|'
        . 'pages\s+(?:in|under|of)|how\s+many\s+pages\s+(?:in|under)|what\s+are\s+the\s+pages'
        . ')\b/iu', $clean)
        || preg_match('/(?:के\s*बारे\s*में\s*बताओ|के\s*बारे\s*में|क्या[-\s]*(?:क्या\s*)?है|कौन[-\s]*(?:कौन\s*)?से\s*पेज|कितने\s*पेज|पेज\s*(?:बताओ|दिखाओ|हैं)|अंदर\s*क्या\s*है|सारे\s*पेज)/u', $raw)
        || (preg_match('/\b(?:section|management)\b/iu', $clean) && preg_match('/\b(?:pages?|kya|kaun|which|what|list|show|batao|dikhao)\b/iu', $clean))
        || (preg_match('/(?:सेक्शन|मैनेजमेंट)/u', $raw) && preg_match('/(?:पेज|क्या|कौन|लिस्ट|बताओ|दिखाओ)/u', $raw));

    // 3. Match section in active panel
    $match = AppNavigationOntology::findSectionByAlias($raw, $panelKey);

    // 4. If no match in active panel, check if it's an inquiry about the OTHER panel's section
    if ($match === null) {
        $otherMatch = AppNavigationOntology::findSectionByAlias($raw, $otherPanelKey);
        if ($otherMatch !== null && $role === 'member') {
            return [
                'success' => false,
                'intent' => 'unauthorized',
                'speech' => "{$otherMatch['data']['name']} Admin portal ka section hai. Member account se yeh access nahi kiya ja sakta.",
            ];
        }
    }

    if ($match !== null) {
        $secKey = $match['key'];
        $secData = $match['data'];

        // 5. Check if user ALSO mentioned a child page of this section
        foreach ($secData['pages'] as $childLabel => $childDestKey) {
            $childLower = mb_strtolower($childLabel, 'UTF-8');
            if ($childLower === mb_strtolower($secData['name'], 'UTF-8') && $isSectionQuestion) {
                continue;
            }
            if (str_contains($clean, $childLower)) {
                return [
                    'intent' => 'navigate',
                    'child_destination' => $childDestKey,
                ];
            }
        }

        // 6. If section question or parent invocation
        if ($isSectionQuestion
            || preg_match('/\b(?:kholo|open|dikhao|chalo|jao|dekho|details?|section)\b/iu', $clean)
            || preg_match('/(?:खोलो|ओपन|दिखाओ|चलो|जाओ|देखो|सेक्शन)/u', $raw)
            || $match['match_length'] >= mb_strlen($clean, 'UTF-8') - 4) {
            return [
                'intent' => 'clarify',
                'section_key' => $secKey,
                'section_name' => $secData['name'],
                'pages' => array_keys($secData['pages']),
            ];
        }
    }

    return null;
}

// Test against all 8 representative utterances + Devanagari
$allTests = [
    ['अर्निंग सेक्शन में कौन-कौन से पेज हैं', 'member', 'clarify', 'earnings'],
    ['ऐड फंड सेक्शन में कौन से पेज हैं', 'member', 'clarify', 'add_fund'],
    ['विड्रॉल सेक्शन में कौन से पेज हैं', 'member', 'clarify', 'withdrawal_section'],
    ['रिवॉर्ड सेक्शन के पेज बताओ', 'member', 'clarify', 'reward_section'],
    ['टीम मैनेजमेंट में कौन-कौन से पेज हैं', 'member', 'clarify', 'team_management'],
    ['पी2पी सेक्शन में कौन से पेज हैं', 'member', 'clarify', 'p2p'],
    ['मुझे अर्निंग सेक्शन के पेज बताओ।', 'member', 'clarify', 'earnings'],
    ['Add Fund section mein kaun-kaun se pages hain?', 'member', 'clarify', 'add_fund'],
    ['Member Management ke baare mein batao.', 'member', 'unauthorized', null],
    ['Earning section ke andar kya-kya hai?', 'member', 'clarify', 'earnings'],
    ['Withdrawal Section mein kaun se pages hain?', 'member', 'clarify', 'withdrawal_section'],
    ['Reward Section ke pages batao.', 'member', 'clarify', 'reward_section'],
    ['Team Management mein kaun-kaun se pages hain?', 'member', 'clarify', 'team_management'],
    ['Payment Management section ke andar kya hai?', 'member', 'unauthorized', null],
    ['P2P section mein kaun-kaun se pages hain?', 'member', 'clarify', 'p2p'],
    ['Show me all pages in the Income section', 'member', 'clarify', 'earnings'],
    ['Earning me kaunse page hai', 'member', 'clarify', 'earnings'],
    ['Member Management mein kaun-kaun se pages hain?', 'admin', 'clarify', 'member_management'],
    ['Payment Management ke options batao.', 'admin', 'clarify', 'payment_management'],
];

echo "--- TESTING IMPROVED SECTION DISCOVERY ---" . PHP_EOL;
$allPass = true;
foreach ($allTests as [$utterance, $role, $expectedIntent, $expectedSec]) {
    $res = testResolveSectionDiscovery($utterance, $role);
    $intent = $res['intent'] ?? 'NONE';
    $sec = $res['section_key'] ?? null;
    $pass = ($intent === $expectedIntent) && ($expectedSec === null || $sec === $expectedSec);
    $status = $pass ? 'PASS' : 'FAIL';
    if (!$pass) $allPass = false;
    echo "[$status] '$utterance' ($role) -> Intent: $intent, Sec: " . ($sec ?? 'none') . PHP_EOL;
}

if ($allPass) {
    echo PHP_EOL . "PERFECT: All 19 test cases PASSED!" . PHP_EOL;
}
