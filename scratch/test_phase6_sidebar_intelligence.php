<?php

/**
 * Phase 6 — Complete Sidebar Intelligence & Natural Voice Conversation Verification Suite
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\IraNavigationService;

$service = app(IraNavigationService::class);

echo "=======================================================\n";
echo "IRA VOICE NAVIGATION — PHASE 6 VERIFICATION SUITE\n";
echo "=======================================================\n\n";

$passCount = 0;
$failCount = 0;

function runTest(string $title, callable $testFn, &$passCount, &$failCount) {
    try {
        $result = $testFn();
        if ($result === true) {
            echo "✅ [PASS] {$title}\n";
            $passCount++;
        } else {
            echo "❌ [FAIL] {$title} — Assertion returned false\n";
            $failCount++;
        }
    } catch (\Throwable $e) {
        echo "❌ [FAIL] {$title} — Exception: " . $e->getMessage() . "\n";
        $failCount++;
    }
}

// -------------------------------------------------------------
// Group 1: Member Section Discovery Inquiries (Natural phrasing)
// -------------------------------------------------------------

runTest('1. Member: "Earnings section ke baare mein batao"', function() use ($service) {
    $res = $service->resolveIntent('Earnings section ke baare mein batao', 'member', '/member/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Earnings'
        && count($res['section_discovery']['pages']) === 3
        && in_array('Direct Income', $res['section_discovery']['pages']);
}, $passCount, $failCount);

runTest('2. Member: "Add Fund section mein kya-kya available hai?"', function() use ($service) {
    $res = $service->resolveIntent('Add Fund section mein kya-kya available hai?', 'member', '/member/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Add Fund'
        && count($res['section_discovery']['pages']) === 2;
}, $passCount, $failCount);

runTest('3. Member: "Reward Section ke pages batao"', function() use ($service) {
    $res = $service->resolveIntent('Reward Section ke pages batao', 'member', '/member/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Reward Section'
        && count($res['section_discovery']['pages']) === 2;
}, $passCount, $failCount);

runTest('4. Member: "Team Management ke baare mein batao"', function() use ($service) {
    $res = $service->resolveIntent('Team Management ke baare mein batao', 'member', '/member/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Team Management'
        && count($res['section_discovery']['pages']) === 3;
}, $passCount, $failCount);

runTest('5. Member: "P2P section mein kya hai?"', function() use ($service) {
    $res = $service->resolveIntent('P2P section mein kya hai?', 'member', '/member/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'P2P'
        && count($res['section_discovery']['pages']) === 2;
}, $passCount, $failCount);

runTest('6. Member: "Support section mein kaun se pages hain?"', function() use ($service) {
    $res = $service->resolveIntent('Support section mein kaun se pages hain?', 'member', '/member/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Support'
        && count($res['section_discovery']['pages']) === 2;
}, $passCount, $failCount);

runTest('7. Member: "Profile section ke baare mein batao"', function() use ($service) {
    $res = $service->resolveIntent('Profile section ke baare mein batao', 'member', '/member/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Profile'
        && count($res['section_discovery']['pages']) === 3;
}, $passCount, $failCount);

runTest('8. Member: "Withdrawal section ke pages batao"', function() use ($service) {
    $res = $service->resolveIntent('Withdrawal section ke pages batao', 'member', '/member/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Withdrawal Section'
        && count($res['section_discovery']['pages']) === 4;
}, $passCount, $failCount);

runTest('9. Member: "Investment section ke andar kya-kya hai?"', function() use ($service) {
    $res = $service->resolveIntent('Investment section ke andar kya-kya hai?', 'member', '/member/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Investment'
        && count($res['section_discovery']['pages']) === 2;
}, $passCount, $failCount);

// -------------------------------------------------------------
// Group 2: Admin Section Discovery Inquiries
// -------------------------------------------------------------

runTest('10. Admin: "Member Management ke baare mein batao"', function() use ($service) {
    $res = $service->resolveIntent('Member Management ke baare mein batao', 'admin', '/admin/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Member Management'
        && count($res['section_discovery']['pages']) === 5;
}, $passCount, $failCount);

runTest('11. Admin: "Funds Management mein kya-kya hai?"', function() use ($service) {
    $res = $service->resolveIntent('Funds Management mein kya-kya hai?', 'admin', '/admin/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Funds Management'
        && count($res['section_discovery']['pages']) === 4;
}, $passCount, $failCount);

runTest('12. Admin: "Staking Section ke pages batao"', function() use ($service) {
    $res = $service->resolveIntent('Staking Section ke pages batao', 'admin', '/admin/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Staking Section'
        && count($res['section_discovery']['pages']) === 2;
}, $passCount, $failCount);

runTest('13. Admin: "Income Section ke baare mein batao"', function() use ($service) {
    $res = $service->resolveIntent('Income Section ke baare mein batao', 'admin', '/admin/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Income Section'
        && count($res['section_discovery']['pages']) === 3;
}, $passCount, $failCount);

runTest('14. Admin: "Payment Management mein kaun-kaun se pages hain?"', function() use ($service) {
    $res = $service->resolveIntent('Payment Management mein kaun-kaun se pages hain?', 'admin', '/admin/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Payment Management'
        && count($res['section_discovery']['pages']) === 3;
}, $passCount, $failCount);

runTest('15. Admin: "Support section ke pages batao"', function() use ($service) {
    $res = $service->resolveIntent('Support section ke pages batao', 'admin', '/admin/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Support'
        && count($res['section_discovery']['pages']) === 3;
}, $passCount, $failCount);

// -------------------------------------------------------------
// Group 3: Multi-Turn Selection, Ordinals & Correction Resiliency
// -------------------------------------------------------------

runTest('16. Multi-turn: Follow-up by page name "Direct Income"', function() use ($service) {
    $step1 = $service->resolveIntent('Earnings section mein kaun se pages hain?', 'member', '/member/dashboard');
    $step2 = $service->resolveIntent('Direct Income', 'member', '/member/dashboard', 'chat', $step1['context']);
    return $step2['intent'] === 'navigate'
        && $step2['destination_key'] === 'member_income_direct'
        && str_contains($step2['target_url'], '/member/income/direct-income');
}, $passCount, $failCount);

runTest('17. Multi-turn: Follow-up by ordinal "Pehla wala" (Staking Income)', function() use ($service) {
    $step1 = $service->resolveIntent('Earnings section ke pages batao', 'member', '/member/dashboard');
    $step2 = $service->resolveIntent('Pehla wala', 'member', '/member/dashboard', 'chat', $step1['context']);
    return $step2['intent'] === 'navigate'
        && $step2['destination_key'] === 'member_income_staking';
}, $passCount, $failCount);

runTest('18. Multi-turn: Follow-up by ordinal "Doosra wala" (Level Income)', function() use ($service) {
    $step1 = $service->resolveIntent('Earnings section ke pages batao', 'member', '/member/dashboard');
    $step2 = $service->resolveIntent('Doosra wala', 'member', '/member/dashboard', 'chat', $step1['context']);
    return $step2['intent'] === 'navigate'
        && $step2['destination_key'] === 'member_income_level';
}, $passCount, $failCount);

runTest('19. Multi-turn: Follow-up by ordinal "Teesra wala" (Direct Income)', function() use ($service) {
    $step1 = $service->resolveIntent('Earnings section ke pages batao', 'member', '/member/dashboard');
    $step2 = $service->resolveIntent('Teesra wala', 'member', '/member/dashboard', 'chat', $step1['context']);
    return $step2['intent'] === 'navigate'
        && $step2['destination_key'] === 'member_income_direct';
}, $passCount, $failCount);

runTest('20. Multi-turn: Correction resiliency "Nahi, doosra" -> navigates to Level Income, does NOT cancel', function() use ($service) {
    $step1 = $service->resolveIntent('Earnings section ke pages batao', 'member', '/member/dashboard');
    $step2 = $service->resolveIntent('Nahi, doosra', 'member', '/member/dashboard', 'chat', $step1['context']);
    return $step2['intent'] === 'navigate'
        && $step2['destination_key'] === 'member_income_level';
}, $passCount, $failCount);

runTest('21. Multi-turn: Clean cancellation "Nahi, rehne do"', function() use ($service) {
    $step1 = $service->resolveIntent('Earnings section ke pages batao', 'member', '/member/dashboard');
    $step2 = $service->resolveIntent('Nahi, rehne do', 'member', '/member/dashboard', 'chat', $step1['context']);
    return $step2['intent'] === 'cancel';
}, $passCount, $failCount);

// -------------------------------------------------------------
// Group 4: Contextual, Embedded Child & Submenu Parent Inquiries
// -------------------------------------------------------------

runTest('22. Contextual: "Is section ke andar kya-kya hai?" while on /member/income/direct-income', function() use ($service) {
    $res = $service->resolveIntent('Is section ke andar kya-kya hai?', 'member', '/member/income/direct-income');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Earnings'
        && count($res['section_discovery']['pages']) === 3;
}, $passCount, $failCount);

runTest('23. Contextual: "Is section ke pages batao" while on /admin/member-details', function() use ($service) {
    $res = $service->resolveIntent('Is section ke pages batao', 'admin', '/admin/member-details');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Member Management'
        && count($res['section_discovery']['pages']) === 5;
}, $passCount, $failCount);

runTest('24. Embedded child page: "Member Management mein Members Details khol do"', function() use ($service) {
    $res = $service->resolveIntent('Member Management mein Members Details khol do', 'admin', '/admin/dashboard');
    return $res['intent'] === 'navigate'
        && $res['destination_key'] === 'admin_member_details'
        && str_contains($res['target_url'], '/admin/member-details');
}, $passCount, $failCount);

runTest('25. Submenu parent query: Admin saying "Member Management kholo"', function() use ($service) {
    $res = $service->resolveIntent('Member Management kholo', 'admin', '/admin/dashboard');
    return $res['intent'] === 'clarify'
        && isset($res['section_discovery'])
        && $res['section_discovery']['section_name'] === 'Member Management';
}, $passCount, $failCount);

// -------------------------------------------------------------
// Group 5: Security & Cross-Role Isolation
// -------------------------------------------------------------

runTest('26. Security: Member asking "Member Management ke baare mein batao" -> unauthorized', function() use ($service) {
    $res = $service->resolveIntent('Member Management ke baare mein batao', 'member', '/member/dashboard');
    return $res['intent'] === 'unauthorized';
}, $passCount, $failCount);

runTest('27. Security: Member asking "Funds Management ke baare mein batao" -> unauthorized', function() use ($service) {
    $res = $service->resolveIntent('Funds Management ke baare mein batao', 'member', '/member/dashboard');
    return $res['intent'] === 'unauthorized';
}, $passCount, $failCount);

runTest('28. Security: Member asking "Admin dashboard kholo" -> unauthorized', function() use ($service) {
    $res = $service->resolveIntent('Admin dashboard kholo', 'member', '/member/dashboard');
    return $res['intent'] === 'unauthorized';
}, $passCount, $failCount);

runTest('29. Authorized: Admin asking "Admin dashboard kholo" -> navigates to admin dashboard', function() use ($service) {
    $res = $service->resolveIntent('Admin dashboard kholo', 'admin', '/admin/member-details');
    return $res['intent'] === 'navigate'
        && $res['destination_key'] === 'admin_dashboard';
}, $passCount, $failCount);

echo "\n-------------------------------------------------------\n";
echo "SUMMARY: {$passCount} Passed, {$failCount} Failed (Total " . ($passCount + $failCount) . ")\n";
echo "-------------------------------------------------------\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
