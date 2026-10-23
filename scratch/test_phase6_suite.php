<?php

/**
 * Quantum ARB IRA Voice Navigation — Phase 6 Verification Test Suite
 * Validates backend contract, section discovery structure, multi-turn state resolution,
 * and JSON payload integrity expected by the frontend voice runtime.
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\IraNavigationService;

$service = app(IraNavigationService::class);

$passed = 0;
$failed = 0;
$total = 0;

function assertTest($description, $condition, $details = '') {
    global $passed, $failed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "✅ [PASS] Test #{$total}: {$description}\n";
    } else {
        $failed++;
        echo "❌ [FAIL] Test #{$total}: {$description}\n";
        if ($details) {
            echo "   DETAILS: {$details}\n";
        }
    }
}

echo "=======================================================\n";
echo "IRA VOICE NAVIGATION — PHASE 6 VERIFICATION TEST SUITE\n";
echo "=======================================================\n\n";

// 1. Member Section Discovery: "Earning section mein kaun-kaun se pages hain?"
$res1 = $service->resolveIntent("Earning section mein kaun-kaun se pages hain?", 'member', '/member/dashboard', 'chat', []);
assertTest(
    'Member queries "Earning section mein kaun-kaun se pages hain?" -> returns clarify intent with section_discovery payload',
    $res1['intent'] === 'clarify' && $res1['needs_clarification'] === true && !empty($res1['speech']) && isset($res1['section_discovery']),
    "Intent: {$res1['intent']}, Speech: {$res1['speech']}"
);

// 2. Member Section Discovery Payload structure for frontend UI rendering
assertTest(
    'Section discovery payload has section_name, pages list, and pending_options context',
    isset($res1['section_discovery']) &&
    strcasecmp($res1['section_discovery']['section_name'], 'Earnings') === 0 &&
    is_array($res1['section_discovery']['pages']) &&
    count($res1['section_discovery']['pages']) >= 3 &&
    isset($res1['context']['pending_options']),
    "Discovery: " . json_encode($res1['section_discovery'] ?? null)
);

// 3. Member Step 2 Follow-up by page name: "Direct Income"
$contextFromStep1 = $res1['context'];
$res2 = $service->resolveIntent("Direct Income", 'member', '/member/dashboard', 'chat', $contextFromStep1);
assertTest(
    'Step 2 follow-up with "Direct Income" navigates to direct income route',
    $res2['intent'] === 'navigate' && !empty($res2['target_url']) && str_contains($res2['target_url'], 'direct-income'),
    "Intent: {$res2['intent']}, Target: {$res2['target_url']}, Speech: {$res2['speech']}"
);

// 4. Member Step 2 Follow-up by ordinal: "Pehla wala"
$res3 = $service->resolveIntent("Pehla wala", 'member', '/member/dashboard', 'chat', $contextFromStep1);
assertTest(
    'Step 2 follow-up with ordinal "Pehla wala" resolves first pending option',
    $res3['intent'] === 'navigate' && !empty($res3['target_url']),
    "Intent: {$res3['intent']}, Target: {$res3['target_url']}, Speech: {$res3['speech']}"
);

// 5. Member Team Section Discovery: "Team section mein kaun se pages hain?"
$res4 = $service->resolveIntent("Team section mein kaun se pages hain?", 'member', '/member/dashboard', 'chat', []);
assertTest(
    'Member queries "Team section mein kaun se pages hain?" -> returns Team pages list in section_discovery',
    $res4['intent'] === 'clarify' &&
    isset($res4['section_discovery']['pages']) &&
    in_array('Direct Members', $res4['section_discovery']['pages']),
    "Pages: " . json_encode($res4['section_discovery']['pages'] ?? [])
);

// 6. Member Step 2 Follow-up with Hindi ordinal: "Doosra wala"
$res5 = $service->resolveIntent("Doosra wala", 'member', '/member/dashboard', 'chat', $res4['context']);
assertTest(
    'Step 2 follow-up with ordinal "Doosra wala" resolves second pending option',
    $res5['intent'] === 'navigate' && !empty($res5['target_url']),
    "Intent: {$res5['intent']}, Target: {$res5['target_url']}, Speech: {$res5['speech']}"
);

// 7. Member P2P Section Discovery: "P2P section mein kya hai?"
$res6 = $service->resolveIntent("P2P section mein kya hai?", 'member', '/member/dashboard', 'chat', []);
assertTest(
    'Member queries "P2P section mein kya hai?" -> lists P2P pages in section_discovery',
    $res6['intent'] === 'clarify' &&
    isset($res6['section_discovery']['section_name']) &&
    $res6['section_discovery']['section_name'] === 'P2P',
    "Intent: {$res6['intent']}, Section: " . ($res6['section_discovery']['section_name'] ?? 'none')
);

// 8. Admin Section Discovery: "Payment management section mein kaun se pages hain?"
$res7 = $service->resolveIntent("Payment management section mein kaun se pages hain?", 'admin', '/admin/dashboard', 'chat', []);
assertTest(
    'Admin queries "Payment management section mein kaun se pages hain?" -> returns Admin Payment pages',
    $res7['intent'] === 'clarify' &&
    isset($res7['section_discovery']['pages']) &&
    in_array('Payment History', $res7['section_discovery']['pages']),
    "Admin Pages: " . json_encode($res7['section_discovery']['pages'] ?? [])
);

// 9. Admin Step 2 Follow-up: "Payment History"
$res8 = $service->resolveIntent("Payment History", 'admin', '/admin/dashboard', 'chat', $res7['context']);
assertTest(
    'Admin chooses "Payment History" -> navigates to admin payment history route',
    $res8['intent'] === 'navigate' && !empty($res8['target_url']),
    "Intent: {$res8['intent']}, Target: {$res8['target_url']}"
);

// 10. Security: Member asks for Admin Dashboard
$res9 = $service->resolveIntent("Admin dashboard kholo", 'member', '/member/dashboard', 'chat', []);
assertTest(
    'Security: Member requesting admin dashboard is rejected with unauthorized',
    $res9['intent'] === 'unauthorized' && $res9['success'] === false,
    "Intent: {$res9['intent']}, Success: " . ($res9['success'] ? 'true' : 'false')
);

// 11. Security: Guest asks for Member Wallet
$res10 = $service->resolveIntent("Mera wallet kholo", 'guest', '/', 'chat', []);
assertTest(
    'Security: Guest requesting wallet is redirected to member login',
    $res10['intent'] === 'unauthorized' && isset($res10['target_url']) && str_contains($res10['target_url'], 'member'),
    "Intent: {$res10['intent']}, Target: " . ($res10['target_url'] ?? 'none')
);

// 12. History navigation: "Pichhle page par le chalo"
$res11 = $service->resolveIntent("Pichhle page par le chalo", 'member', '/member/earning/direct-income', 'chat', []);
assertTest(
    'User commands "Pichhle page par le chalo" -> returns go_back intent',
    $res11['intent'] === 'go_back',
    "Intent: {$res11['intent']}, Speech: {$res11['speech']}"
);

// 13. History navigation: "Aage wale page par jao"
$res12 = $service->resolveIntent("Aage wale page par jao", 'member', '/member/earning/direct-income', 'chat', []);
assertTest(
    'User commands "Aage wale page par jao" -> returns go_forward intent',
    $res12['intent'] === 'go_forward',
    "Intent: {$res12['intent']}, Speech: {$res12['speech']}"
);

// 14. Assistant close: "Window band kar do"
$res13 = $service->resolveIntent("Window band kar do", 'member', '/member/dashboard', 'chat', []);
assertTest(
    'User commands "Window band kar do" -> returns close_assistant intent',
    $res13['intent'] === 'close_assistant',
    "Intent: {$res13['intent']}, Speech: {$res13['speech']}"
);

// 15. Clarification cancellation: "Nahi"
$res14 = $service->resolveIntent("Nahi, mujhe nahi jaana", 'member', '/member/dashboard', 'chat', $contextFromStep1);
assertTest(
    'User says "Nahi, mujhe nahi jaana" during pending options -> returns cancel intent',
    $res14['intent'] === 'cancel',
    "Intent: {$res14['intent']}, Speech: {$res14['speech']}"
);

// 16. JSON Contract Integrity: Required keys for ira-assistant.js
$requiredKeys = ['success', 'intent', 'speech', 'target_url', 'destination_key', 'context', 'section_discovery'];
$hasAllKeys = true;
foreach ($requiredKeys as $k) {
    if (!array_key_exists($k, $res1)) {
        $hasAllKeys = false;
        break;
    }
}
assertTest(
    'Response payload contains all required contract keys for frontend consumption',
    $hasAllKeys,
    "Missing key in: " . implode(', ', array_keys($res1))
);

// 17. Speech Utterance Cleanliness: No markdown or bracketed artifacts in spoken speech
$res15 = $service->resolveIntent("Earning section mein kaun-kaun se pages hain?", 'member', '/member/dashboard', 'chat', []);
$speechClean = !preg_match('/[*#_`\[\]]/', $res15['speech']);
assertTest(
    'Spoken speech contains clean phonetic phrasing without raw markdown artifacts',
    $speechClean,
    "Speech: {$res15['speech']}"
);

// 18. STT phonetic error handling: "Petrol history" -> maps to P2P wallet history
$res16 = $service->resolveIntent("Petrol history", 'member', '/member/dashboard', 'chat', []);
assertTest(
    'Phonetic STT error "Petrol history" safely resolves to P2P wallet history',
    $res16['intent'] === 'navigate' && str_contains($res16['target_url'] ?? '', 'p2p-wallet-history'),
    "Intent: {$res16['intent']}, Target: " . ($res16['target_url'] ?? 'none')
);

echo "\n-------------------------------------------------------\n";
echo "PHASE 6 SUMMARY: {$passed} Passed, {$failed} Failed (Total {$total})\n";
echo "-------------------------------------------------------\n";

exit($failed > 0 ? 1 : 0);
