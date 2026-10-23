<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\IraNavigationService;

$service = app(IraNavigationService::class);

echo "=======================================================\n";
echo "IRA VOICE NAVIGATION — PHASE 5 COMPLETE TEST SUITE\n";
echo "=======================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function runTest($id, $description, $callable) {
    global $testsPassed, $testsFailed;
    try {
        $result = $callable();
        if ($result === true) {
            echo "✅ [PASS] Test #{$id}: {$description}\n";
            $testsPassed++;
        } else {
            echo "❌ [FAIL] Test #{$id}: {$description}\n";
            echo "   Reason: " . ($result ?: "Returned false") . "\n";
            $testsFailed++;
        }
    } catch (\Throwable $e) {
        echo "💥 [ERROR] Test #{$id}: {$description}\n";
        echo "   Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        $testsFailed++;
    }
}

// Test 1: Section Discovery — "Earning section mein kaun-kaun se pages hain?"
$ctx1 = [];
runTest(1, 'Member asks: "Earning section mein kaun-kaun se pages hain?"', function() use ($service, &$ctx1) {
    $res = $service->resolveIntent(
        'Earning section mein kaun-kaun se pages hain?',
        'member',
        '/member/dashboard'
    );
    $ctx1 = $res['context'] ?? [];
    if ($res['intent'] !== 'clarify') return "Expected intent 'clarify', got '{$res['intent']}'";
    if (!$res['needs_clarification']) return "Expected needs_clarification=true";
    if (empty($ctx1['pending_clarification']) || $ctx1['pending_clarification'] !== 'section_page_choice') {
        return "Expected pending_clarification='section_page_choice'";
    }
    if (empty($ctx1['pending_options']) || count($ctx1['pending_options']) !== 3) {
        return "Expected 3 child pages in pending_options";
    }
    if (!str_contains($res['speech'], 'Staking Income') || !str_contains($res['speech'], 'Direct Income')) {
        return "Expected speech to list Staking Income and Direct Income. Got: {$res['speech']}";
    }
    return true;
});

// Test 2: Step 2 Follow-up — "Direct Income"
runTest(2, 'Step 2 Follow-up: User chooses "Direct Income"', function() use ($service, &$ctx1) {
    $res = $service->resolveIntent(
        'Direct Income',
        'member',
        '/member/dashboard',
        'chat',
        $ctx1
    );
    if ($res['intent'] !== 'navigate') return "Expected intent 'navigate', got '{$res['intent']}'";
    if ($res['destination_key'] !== 'member_income_direct') return "Expected destination 'member_income_direct', got '{$res['destination_key']}'";
    if (!str_contains($res['target_url'], '/member/income/direct-incomes')) return "Expected URL with /member/income/direct-incomes, got '{$res['target_url']}'";
    return true;
});

// Test 3: Section Discovery — "Team section mein kaun se pages hain?"
$ctx3 = [];
runTest(3, 'Member asks: "Team section mein kaun se pages hain?"', function() use ($service, &$ctx3) {
    $res = $service->resolveIntent(
        'Team section mein kaun se pages hain?',
        'member',
        '/member/dashboard'
    );
    $ctx3 = $res['context'] ?? [];
    if ($res['intent'] !== 'clarify') return "Expected intent 'clarify', got '{$res['intent']}'";
    if (!$res['needs_clarification']) return "Expected needs_clarification=true";
    if (empty($ctx3['pending_options']) || count($ctx3['pending_options']) !== 3) {
        return "Expected 3 child pages in team management pending_options";
    }
    if (!str_contains($res['speech'], 'Geneology') || !str_contains($res['speech'], 'Direct Members')) {
        return "Expected speech to list Geneology and Direct Members. Got: {$res['speech']}";
    }
    return true;
});

// Test 4: Step 2 Follow-up — "Pehla wala"
runTest(4, 'Step 2 Follow-up: User chooses ordinal "Pehla wala"', function() use ($service, &$ctx3) {
    $res = $service->resolveIntent(
        'Pehla wala',
        'member',
        '/member/dashboard',
        'chat',
        $ctx3
    );
    if ($res['intent'] !== 'navigate') return "Expected intent 'navigate', got '{$res['intent']}'";
    if ($res['destination_key'] !== 'member_team_geneology') return "Expected destination 'member_team_geneology', got '{$res['destination_key']}'";
    if (!str_contains($res['target_url'], '/member/team/geneology')) return "Expected URL with /member/team/geneology, got '{$res['target_url']}'";
    return true;
});

// Test 5: Section Discovery — "Investment section mein kya-kya hai?"
runTest(5, 'Member asks: "Investment section mein kya-kya hai?"', function() use ($service) {
    $res = $service->resolveIntent(
        'Investment section mein kya-kya hai?',
        'member',
        '/member/dashboard'
    );
    if ($res['intent'] !== 'clarify') return "Expected intent 'clarify', got '{$res['intent']}'";
    if (!str_contains($res['speech'], 'Create Investment') || !str_contains($res['speech'], 'Investment Details')) {
        return "Expected speech to list Create Investment and Investment Details. Got: {$res['speech']}";
    }
    return true;
});

// Test 6: Section Discovery — "P2P section mein kaun se pages hain?"
runTest(6, 'Member asks: "P2P section mein kaun se pages hain?"', function() use ($service) {
    $res = $service->resolveIntent(
        'P2P section mein kaun se pages hain?',
        'member',
        '/member/dashboard'
    );
    if ($res['intent'] !== 'clarify') return "Expected intent 'clarify', got '{$res['intent']}'";
    if (!str_contains($res['speech'], 'P2P Wallet') || !str_contains($res['speech'], 'P2P History')) {
        return "Expected speech to list P2P Wallet and P2P History. Got: {$res['speech']}";
    }
    return true;
});

// Test 7: Phonetic / Direct P2P History — "Petrol history" & "P2P History"
runTest(7, 'Member asks: "Petrol history" (STT transcription artifact)', function() use ($service) {
    $res = $service->resolveIntent(
        'Petrol history',
        'member',
        '/member/dashboard'
    );
    if ($res['intent'] !== 'navigate') return "Expected intent 'navigate', got '{$res['intent']}'";
    if ($res['destination_key'] !== 'member_p2p_history') return "Expected destination 'member_p2p_history', got '{$res['destination_key']}'";
    if (!str_contains($res['target_url'], '/member/p2p-wallet-history')) return "Expected URL with /member/p2p-wallet-history, got '{$res['target_url']}'";
    return true;
});

// Test 8: Direct P2P Wallet — "P2P Wallet khol do"
runTest(8, 'Member asks: "P2P Wallet khol do"', function() use ($service) {
    $res = $service->resolveIntent(
        'P2P Wallet khol do',
        'member',
        '/member/dashboard'
    );
    if ($res['intent'] !== 'navigate') return "Expected intent 'navigate', got '{$res['intent']}'";
    if ($res['destination_key'] !== 'member_p2p_wallet') return "Expected destination 'member_p2p_wallet', got '{$res['destination_key']}'";
    if (!str_contains($res['target_url'], '/member/p2p-wallet')) return "Expected URL with /member/p2p-wallet, got '{$res['target_url']}'";
    return true;
});

// Test 9: Browser Navigation Back — "Pichhle page par le chalo"
runTest(9, 'User commands: "Pichhle page par le chalo"', function() use ($service) {
    $res = $service->resolveIntent(
        'Pichhle page par le chalo',
        'member',
        '/member/geneology'
    );
    if ($res['intent'] !== 'go_back') return "Expected intent 'go_back', got '{$res['intent']}'";
    return true;
});

// Test 10: Browser Navigation Forward — "Aage wale page par jao"
runTest(10, 'User commands: "Aage wale page par jao"', function() use ($service) {
    $res = $service->resolveIntent(
        'Aage wale page par jao',
        'member',
        '/member/dashboard'
    );
    if ($res['intent'] !== 'go_forward') return "Expected intent 'go_forward', got '{$res['intent']}'";
    return true;
});

// Test 11: Close Assistant Window — "Window band kar do"
runTest(11, 'User commands: "Window band kar do"', function() use ($service) {
    $res = $service->resolveIntent(
        'Window band kar do',
        'member',
        '/member/dashboard'
    );
    if ($res['intent'] !== 'close_assistant') return "Expected intent 'close_assistant', got '{$res['intent']}'";
    return true;
});

// Test 12: Cross-role Security — Member requests "Admin dashboard kholo"
runTest(12, 'Security check: Member asks for "Admin dashboard kholo"', function() use ($service) {
    $res = $service->resolveIntent(
        'Admin dashboard kholo',
        'member',
        '/member/dashboard'
    );
    if ($res['intent'] !== 'unauthorized') return "Expected intent 'unauthorized', got '{$res['intent']}'";
    if ($res['success'] !== false) return "Expected success=false";
    return true;
});

// Test 13: Cross-role Security — Member requests "Member details dikhao" (Admin only)
runTest(13, 'Security check: Member asks for "Member details dikhao"', function() use ($service) {
    $res = $service->resolveIntent(
        'Member details dikhao',
        'member',
        '/member/dashboard'
    );
    if ($res['intent'] !== 'unauthorized') return "Expected intent 'unauthorized', got '{$res['intent']}'";
    if ($res['success'] !== false) return "Expected success=false";
    return true;
});

// Test 14: Admin Navigation — Admin requests "Admin dashboard kholo"
runTest(14, 'Admin asks: "Admin dashboard kholo"', function() use ($service) {
    $res = $service->resolveIntent(
        'Admin dashboard kholo',
        'admin',
        '/admin/member-detail'
    );
    if ($res['intent'] !== 'navigate') return "Expected intent 'navigate', got '{$res['intent']}'";
    if ($res['destination_key'] !== 'admin_dashboard') return "Expected destination 'admin_dashboard', got '{$res['destination_key']}'";
    if (!str_contains($res['target_url'], '/admin/dashboard')) return "Expected URL with /admin/dashboard, got '{$res['target_url']}'";
    return true;
});

// Test 15: Admin Section Discovery — "Payment management section mein kaun-kaun se pages hain?"
$ctx15 = [];
runTest(15, 'Admin asks: "Payment management section mein kaun-kaun se pages hain?"', function() use ($service, &$ctx15) {
    $res = $service->resolveIntent(
        'Payment management section mein kaun-kaun se pages hain?',
        'admin',
        '/admin/dashboard'
    );
    $ctx15 = $res['context'] ?? [];
    if ($res['intent'] !== 'clarify') return "Expected intent 'clarify', got '{$res['intent']}'";
    if (!str_contains($res['speech'], 'Payment History') || !str_contains($res['speech'], 'Cancelled Requests')) {
        return "Expected speech to list Payment History and Cancelled Requests. Got: {$res['speech']}";
    }
    return true;
});

// Test 16: Admin Step 2 Follow-up — "Payment History"
runTest(16, 'Admin Step 2 Follow-up: User chooses "Payment History"', function() use ($service, &$ctx15) {
    $res = $service->resolveIntent(
        'Payment History',
        'admin',
        '/admin/dashboard',
        'chat',
        $ctx15
    );
    if ($res['intent'] !== 'navigate') return "Expected intent 'navigate', got '{$res['intent']}'";
    if ($res['destination_key'] !== 'admin_payment_history') return "Expected destination 'admin_payment_history', got '{$res['destination_key']}'";
    if (!str_contains($res['target_url'], '/admin/payment-history')) return "Expected URL with /admin/payment-history, got '{$res['target_url']}'";
    return true;
});

// Test 17: Logout Confirmation Flow — "Logout kar do"
$ctx17 = [];
runTest(17, 'Member commands: "Logout kar do"', function() use ($service, &$ctx17) {
    $res = $service->resolveIntent(
        'Logout kar do',
        'member',
        '/member/dashboard'
    );
    $ctx17 = $res['context'] ?? [];
    if ($res['intent'] !== 'logout_prompt') return "Expected intent 'logout_prompt', got '{$res['intent']}'";
    if (empty($ctx17['pending_clarification']) || $ctx17['pending_clarification'] !== 'logout_confirm') {
        return "Expected pending_clarification='logout_confirm'";
    }
    return true;
});

// Test 18: Logout Cancel — "Nahi"
runTest(18, 'Member confirms cancellation: "Nahi"', function() use ($service, &$ctx17) {
    $res = $service->resolveIntent(
        'Nahi',
        'member',
        '/member/dashboard',
        'chat',
        $ctx17
    );
    if ($res['intent'] !== 'cancel') return "Expected intent 'cancel', got '{$res['intent']}'";
    if ($res['success'] !== true) return "Expected success=true";
    return true;
});

echo "\n-------------------------------------------------------\n";
echo "SUMMARY: {$testsPassed} Passed, {$testsFailed} Failed (Total 18)\n";
echo "-------------------------------------------------------\n";

exit($testsFailed === 0 ? 0 : 1);
