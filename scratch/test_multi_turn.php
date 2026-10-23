<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\AppNavigationOntology;

function testResolvePendingClarification(string $clean, string $role, string $lang, array $context)
{
    $pendingType = $context['pending_clarification'] ?? '';

    // E. Section Page Choice Follow-up
    if ($pendingType === 'section_page_choice') {
        $cleanLower = mb_strtolower(trim($clean), 'UTF-8');

        // 1. Re-listing pages if user says "saare pages batao" or "list all pages"
        if (preg_match('/\b(?:saare|all|sabhi|sab)\s+pages?\b/iu', $cleanLower) || preg_match('/(?:सारे|सभी|सब)\s*पेज/u', $clean)) {
            return ['action' => 'relist'];
        }

        // 2. Cancellation check (unless an ordinal or option keyword is present, e.g. "Nahi, doosra page")
        $hasChoicePayload = preg_match('/\b(pehla|first|1st|ek\s+number|number\s+1|doosra|dusra|second|2nd|do\s+number|number\s+2|teesra|tisra|third|3rd|teen\s+number|chautha|fourth|4th|panchva|fifth|5th|aakhri|last)\b/iu', $cleanLower)
            || preg_match('/(?<!kar\s|khol\s|bata\s|dikha\s)\b(?:ek|do|teen|chaar|paanch)\b/iu', $cleanLower)
            || preg_match('/(?:पहला|फर्स्ट|1st|दूसरा|दुसरा|सेकंड|2nd|तीसरा|थर्ड|3rd|चौथा|फोर्थ|4th|पांचवां|फिफ्थ|5th|आखिरी|लास्ट)/u', $clean);

        $sectionPages = $context['pending_options'] ?? [];
        if (!$hasChoicePayload && !empty($sectionPages)) {
            foreach ($sectionPages as $optLabel => $optDest) {
                if (str_contains($cleanLower, mb_strtolower($optLabel, 'UTF-8'))) {
                    $hasChoicePayload = true;
                    break;
                }
            }
        }

        if (!$hasChoicePayload && (preg_match('/\b(nahi|no|nope|cancel|mat|mat\s*karo|rehne\s*do|ruk\s*jao|chhoro|stop|dont|don\'t)\b/iu', $cleanLower)
            || preg_match('/(?:नहीं|ना|कैंसिल|मत\s*करो|रहने\s*दो)/u', $clean))) {
            return [
                'intent' => 'cancel',
            ];
        }

        // 3. Fallthrough for unrelated commands (e.g. "dashboard kholo", "logout")
        if (preg_match('/\b(dashboard|logout|trading|geneology)\b/iu', $cleanLower)) {
            return null; // fallthrough to normal resolution
        }

        if (!empty($sectionPages)) {
            $pageKeys = array_values($sectionPages);
            $pageLabels = array_keys($sectionPages);
            $totalOptions = count($pageKeys);

            // Ordinal matching
            $selectedIndex = null;
            if (preg_match('/\b(?:pehla|first|1st)\b/iu', $cleanLower) || preg_match('/(?<!kar\s)\b(?:ek)\b/iu', $cleanLower) || preg_match('/(?:पहला|फर्स्ट|1st)/u', $clean)) {
                $selectedIndex = 0;
            } elseif (preg_match('/\b(?:doosra|dusra|second|2nd|do\s+number|number\s+2)\b/iu', $cleanLower) || preg_match('/(?<!kar\s|khol\s|bata\s|dikha\s)\b(?:do)\b/iu', $cleanLower) || preg_match('/(?:दूसरा|दुसरा|सेकंड|2nd)/u', $clean)) {
                $selectedIndex = 1;
            } elseif (preg_match('/\b(?:teesra|tisra|third|3rd|teen)\b/iu', $cleanLower) || preg_match('/(?:तीसरा|थर्ड|3rd)/u', $clean)) {
                $selectedIndex = 2;
            } elseif (preg_match('/\b(?:chautha|fourth|4th|chaar)\b/iu', $cleanLower) || preg_match('/(?:चौथा|फोर्थ|4th)/u', $clean)) {
                $selectedIndex = 3;
            } elseif (preg_match('/\b(?:panchva|fifth|5th|paanch)\b/iu', $cleanLower) || preg_match('/(?:पांचवां|फिफ्थ|5th)/u', $clean)) {
                $selectedIndex = 4;
            } elseif (preg_match('/\b(?:aakhri|last)\b/iu', $cleanLower) || preg_match('/(?:आखिरी|लास्ट)/u', $clean)) {
                $selectedIndex = $totalOptions - 1;
            }

            if ($selectedIndex !== null && isset($pageKeys[$selectedIndex])) {
                return [
                    'intent' => 'navigate',
                    'dest' => $pageKeys[$selectedIndex],
                ];
            }

            // Keyword match in pending options
            foreach ($sectionPages as $label => $destKey) {
                $labelLower = mb_strtolower($label, 'UTF-8');
                if (str_contains($cleanLower, $labelLower)) {
                    return [
                        'intent' => 'navigate',
                        'dest' => $destKey,
                    ];
                }
            }

            // Distinctive partial token match (e.g. "history", "security", "details")
            $stopWords = ['page', 'pages', 'wala', 'wali', 'wale', 'par', 'pe', 'kholo', 'dikhao', 'section', 'mein', 'in', 'open', 'show', 'the', 'le', 'chalo', 'jao', 'karo', 'kar', 'do', 'please', 'mujhe', 'khol', 'nahi'];
            $cleanTokens = array_diff(preg_split('/\s+/u', $cleanLower), $stopWords);

            $bestDest = null;
            $maxMatches = 0;

            foreach ($sectionPages as $label => $destKey) {
                $labelTokens = array_diff(preg_split('/\s+/u', mb_strtolower($label, 'UTF-8')), $stopWords);
                $matches = count(array_intersect($cleanTokens, $labelTokens));
                if ($matches > $maxMatches) {
                    $maxMatches = $matches;
                    $bestDest = $destKey;
                }
            }

            if ($bestDest !== null && $maxMatches > 0) {
                return [
                    'intent' => 'navigate',
                    'dest' => $bestDest,
                ];
            }
        }
    }

    return null;
}

$context = [
    'pending_clarification' => 'section_page_choice',
    'pending_section' => 'add_fund',
    'pending_options' => [
        'Add Fund' => 'member_fund_import',
        'Add Fund History' => 'member_fund_history',
    ],
];

$followUps = [
    ['History wala page kholo', 'member_fund_history'],
    ['Pehla wala', 'member_fund_import'],
    ['Doosra page', 'member_fund_history'],
    ['Nahi, doosra page', 'member_fund_history'],
    ['दूसरा पेज', 'member_fund_history'],
    ['पहला वाला', 'member_fund_import'],
    ['Nahi, cancel kar do', 'CANCEL'],
    ['Dashboard kholo', 'FALLTHROUGH'],
];

echo "--- TESTING MULTI-TURN FOLLOW-UP SELECTION ---" . PHP_EOL;
foreach ($followUps as [$utterance, $expected]) {
    $res = testResolvePendingClarification($utterance, 'member', 'hinglish', $context);
    if ($expected === 'CANCEL') {
        $status = (($res['intent'] ?? '') === 'cancel') ? 'PASS' : 'FAIL';
        echo "[$status] '$utterance' -> Expected: CANCEL, Got: " . ($res['intent'] ?? 'none') . PHP_EOL;
    } elseif ($expected === 'FALLTHROUGH') {
        $status = ($res === null) ? 'PASS' : 'FAIL';
        echo "[$status] '$utterance' -> Expected: FALLTHROUGH, Got: " . ($res['dest'] ?? ($res['intent'] ?? 'null')) . PHP_EOL;
    } else {
        $dest = $res['dest'] ?? 'NONE';
        $status = ($dest === $expected) ? 'PASS' : 'FAIL';
        echo "[$status] '$utterance' -> Expected: $expected, Got: $dest" . PHP_EOL;
    }
}
