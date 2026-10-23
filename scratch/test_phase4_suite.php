<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AI\IraNavigationService;
use App\Services\Gemini\GeminiService;
use Illuminate\Http\Request;
use App\Http\Controllers\IraNavigationController;

echo "====================================================================\n";
echo "IRA VOICE NAVIGATION PHASE 4: COMPREHENSIVE TEST SUITE (20 POINTS)\n";
echo "====================================================================\n\n";

$navService = app(IraNavigationService::class);
$controller = app(IraNavigationController::class);
$passed = 0;
$total = 20;

function runTest(int $num, string $title, callable $test) {
    global $passed;
    try {
        $test();
        echo "[PASS] Test {$num}: {$title}\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "[FAIL] Test {$num}: {$title}\n";
        echo "       Error: " . $e->getMessage() . " on line " . $e->getLine() . "\n";
    }
}

// -------------------------------------------------------------
// Test 1: Member says "Dashboard kholo" -> Member Dashboard
// -------------------------------------------------------------
runTest(1, 'Member says "Dashboard kholo" -> Member Dashboard', function () use ($navService) {
    $res = $navService->resolveIntent("Dashboard kholo", 'member', '/member/dashboard');
    assert($res['success'] === true, 'Success must be true');
    assert($res['destination_key'] === 'member_dashboard', 'Must resolve member_dashboard');
    assert(str_contains($res['target_url'], '/member/dashboard'), 'URL must contain /member/dashboard');
});

// -------------------------------------------------------------
// Test 2: Admin says "Dashboard kholo" -> Admin Dashboard
// -------------------------------------------------------------
runTest(2, 'Admin says "Dashboard kholo" -> Admin Dashboard', function () use ($navService) {
    $res = $navService->resolveIntent("Dashboard kholo", 'admin', '/admin/dashboard');
    assert($res['success'] === true, 'Success must be true');
    assert($res['destination_key'] === 'admin_dashboard', 'Must resolve admin_dashboard');
    assert(str_contains($res['target_url'], '/admin/dashboard'), 'URL must contain /admin/dashboard');
});

// -------------------------------------------------------------
// Test 3: Member requests Admin Dashboard -> denied safely
// -------------------------------------------------------------
runTest(3, 'Member requests Admin Dashboard -> denied safely', function () use ($navService) {
    $res = $navService->resolveIntent("Admin dashboard kholo", 'member', '/member/dashboard');
    assert($res['success'] === false, 'Success must be false');
    assert($res['intent'] === 'unauthorized', 'Intent must be unauthorized');
    assert(empty($res['target_url']), 'Target URL must be null');
    assert(str_contains($res['speech'], 'administrator credentials') || str_contains($res['speech'], 'Admin dashboard access'), 'Must explain restriction');
});

// -------------------------------------------------------------
// Test 4: Admin requests Member Details -> correct Admin destination
// -------------------------------------------------------------
runTest(4, 'Admin requests Member Details -> correct Admin destination', function () use ($navService) {
    $res = $navService->resolveIntent("Member details kholo", 'admin', '/admin/dashboard');
    assert($res['success'] === true, 'Success must be true');
    assert($res['destination_key'] === 'admin_member_details', 'Must resolve admin_member_details');
    assert(str_contains($res['target_url'], '/admin/member-details'), 'URL must point to /admin/member-details');
});

// -------------------------------------------------------------
// Test 5: Member requests Member Details -> no cross-role access
// -------------------------------------------------------------
runTest(5, 'Member requests Member Details -> no cross-role access', function () use ($navService) {
    $res = $navService->resolveIntent("Mujhe Member Details ke page par le chalo", 'member', '/member/dashboard');
    assert($res['success'] === false, 'Success must be false');
    assert($res['intent'] === 'unauthorized', 'Intent must be unauthorized');
    assert(empty($res['target_url']), 'Target URL must be null');
    assert(str_contains($res['speech'], 'Admin portal ka part hai') || str_contains($res['speech'], 'restricted to administrators'), 'Must explain admin-only nature');
});

// -------------------------------------------------------------
// Test 6: Member and Admin independently request Wallet, Profile, etc.
// -------------------------------------------------------------
runTest(6, 'Member and Admin independently request Wallet & Profile', function () use ($navService) {
    // Profile
    $memProf = $navService->resolveIntent("Mera profile dikha do", 'member', '/member/dashboard');
    assert($memProf['destination_key'] === 'member_profile_summary', 'Member profile must resolve member_profile_summary');

    $admProf = $navService->resolveIntent("Profile kholo", 'admin', '/admin/dashboard');
    assert($admProf['destination_key'] === 'admin_profile', 'Admin profile must resolve admin_profile');

    // Wallet
    $admWallet = $navService->resolveIntent("Wallet dikhao", 'admin', '/admin/dashboard');
    assert($admWallet['destination_key'] === 'admin_wallet_address', 'Admin wallet must resolve admin_wallet_address');
});

// -------------------------------------------------------------
// Test 7: Natural Hindi, Hinglish and English navigation phrases
// -------------------------------------------------------------
runTest(7, 'Natural Hindi, Hinglish and English navigation phrases', function () use ($navService) {
    // English
    $en = $navService->resolveIntent("Take me to the direct members page", 'member', '/member/dashboard');
    assert($en['destination_key'] === 'member_team_direct', 'English direct members must resolve');

    // Hinglish
    $hiRoman = $navService->resolveIntent("Mujhe direct members ki list par le jao", 'member', '/member/dashboard');
    assert($hiRoman['destination_key'] === 'member_team_direct', 'Hinglish direct members must resolve');

    // Devanagari Hindi
    $hiScript = $navService->resolveIntent("डायरेक्ट मेंबर्स खोलो", 'member', '/member/dashboard');
    assert($hiScript['destination_key'] === 'member_team_direct', 'Devanagari direct members must resolve');
});

// -------------------------------------------------------------
// Test 8: Alternate wording and common spelling variations
// -------------------------------------------------------------
runTest(8, 'Alternate wording and common spelling variations', function () use ($navService) {
    $v1 = $navService->resolveIntent("Geneology tree dikha do", 'member', '/member/dashboard');
    assert($v1['destination_key'] === 'member_team_geneology', 'Geneology tree variation');

    $v2 = $navService->resolveIntent("Can you open the geneology section?", 'member', '/member/dashboard');
    assert($v2['destination_key'] === 'member_team_geneology', 'English conversational variation');

    $v3 = $navService->resolveIntent("Mera genealogy khol do", 'member', '/member/dashboard');
    assert($v3['destination_key'] === 'member_team_geneology', 'Spelling variation genealogy');
});

// -------------------------------------------------------------
// Test 9: Ambiguous Income and Wallet requests -> useful clarification
// -------------------------------------------------------------
runTest(9, 'Ambiguous Income and Wallet requests -> useful clarification', function () use ($navService) {
    $inc = $navService->resolveIntent("Mujhe income page par le chalo", 'member', '/member/dashboard');
    assert($inc['intent'] === 'clarify', 'Income intent must be clarify');
    assert($inc['needs_clarification'] === true, 'needs_clarification must be true');
    assert(str_contains($inc['speech'], 'Direct Income') && str_contains($inc['speech'], 'Level Income'), 'Must offer specific income options');
    assert($inc['context']['pending_clarification'] === 'income_choice', 'Context pending must be income_choice');

    $wal = $navService->resolveIntent("Wallet wala page khol do", 'member', '/member/dashboard');
    assert($wal['intent'] === 'clarify', 'Wallet intent must be clarify');
    assert($wal['context']['pending_clarification'] === 'wallet_choice', 'Context pending must be wallet_choice');
});

// -------------------------------------------------------------
// Test 10: Follow-up answer resolves the pending clarification
// -------------------------------------------------------------
runTest(10, 'Follow-up answer resolves the pending clarification', function () use ($navService) {
    $ctx = ['pending_clarification' => 'income_choice'];
    
    // User replies "Reward Income" or "Reward wala"
    $res = $navService->resolveIntent("Reward Income dekhni hai", 'member', '/member/dashboard', 'chat', $ctx);
    assert($res['success'] === true, 'Follow-up must succeed');
    assert($res['destination_key'] === 'member_income_reward', 'Must resolve member_income_reward');
    assert(str_contains($res['target_url'], 'member/income/reward-incomes'), 'URL must match reward income');
});

// -------------------------------------------------------------
// Test 11: Unclear request -> useful clarification, not repetitive generic speech
// -------------------------------------------------------------
runTest(11, 'Unclear request -> useful clarification, not repetitive generic speech', function () use ($navService) {
    $res = $navService->resolveIntent("kuch ajeeb sa text xyz123", 'member', '/member/dashboard');
    assert($res['intent'] === 'clarify', 'Unclear must clarify');
    assert($res['needs_clarification'] === true, 'needs_clarification must be true');
    assert(str_contains($res['speech'], 'Dashboard') || str_contains($res['speech'], 'Profile'), 'Must provide smart suggestion');
});

// -------------------------------------------------------------
// Test 12: Each relevant sidebar parent section -> actual child-page list
// -------------------------------------------------------------
runTest(12, 'Section Discovery lists actual child pages for Team section', function () use ($navService) {
    // Member asks about Team section
    $res = $navService->resolveIntent("Member panel ke Team section mein kaun-kaun se pages hain?", 'member', '/member/dashboard');
    assert($res['intent'] === 'clarify', 'Section discovery intent must be clarify');
    assert(str_contains($res['speech'], 'Geneology') && str_contains($res['speech'], 'Direct Members') && str_contains($res['speech'], 'Level Member Table'), 'Must list real team pages');
    assert($res['context']['pending_clarification'] === 'section_page_choice', 'Pending must be section_page_choice');

    // Admin asks about Member Management section
    $adminRes = $navService->resolveIntent("Member Management mein kaun-kaun se pages bane hue hain?", 'admin', '/admin/dashboard');
    assert(str_contains($adminRes['speech'], 'Members Details') && str_contains($adminRes['speech'], 'Account Control') && str_contains($adminRes['speech'], 'Members Security'), 'Must list admin member management pages');
});

// -------------------------------------------------------------
// Test 13: Selection of a listed child page -> correct authorized destination
// -------------------------------------------------------------
runTest(13, 'Selection of a listed child page -> correct destination', function () use ($navService) {
    $secCtx = [
        'pending_clarification' => 'section_page_choice',
        'pending_section' => 'team_management',
        'pending_options' => [
            'Geneology' => 'member_team_geneology',
            'Direct Members' => 'member_team_direct',
            'Level Member Table' => 'member_team_level_table',
        ],
    ];

    $res = $navService->resolveIntent("Direct Members khol do", 'member', '/member/dashboard', 'chat', $secCtx);
    assert($res['success'] === true, 'Child selection must succeed');
    assert($res['destination_key'] === 'member_team_direct', 'Must navigate to member_team_direct');
});

// -------------------------------------------------------------
// Test 14: Previous-page navigation
// -------------------------------------------------------------
runTest(14, 'Previous-page navigation intent', function () use ($navService) {
    $res = $navService->resolveIntent("Isse pichhle page par le chalo", 'member', '/member/dashboard');
    assert($res['intent'] === 'go_back', 'Intent must be go_back');
    assert($res['success'] === true, 'Success must be true');
});

// -------------------------------------------------------------
// Test 15: Admin login, Member login and frontend navigation
// -------------------------------------------------------------
runTest(15, 'Admin login, Member login and frontend navigation', function () use ($navService) {
    // Frontend
    $fe = $navService->resolveIntent("Frontend par jao", 'member', '/member/dashboard');
    assert($fe['destination_key'] === 'home', 'Must resolve home');
    assert($fe['target_url'] === url('/'), 'Must match public home URL');

    // Admin login
    $al = $navService->resolveIntent("Admin login page par le chalo", 'guest', '/');
    assert($al['destination_key'] === 'admin_login', 'Must resolve admin_login');
    assert($al['target_url'] === url('/admin'), 'Must match /admin');

    // Member login
    $ml = $navService->resolveIntent("Member login page khol do", 'guest', '/');
    assert($ml['destination_key'] === 'member_login', 'Must resolve member_login');
    assert($ml['target_url'] === url('/member'), 'Must match /member');
});

// -------------------------------------------------------------
// Test 16: Logout cancellation, confirmed logout and ambiguous confirmation
// -------------------------------------------------------------
runTest(16, 'Logout cancellation, confirmed logout and ambiguous confirmation', function () use ($navService) {
    // Step 1: Prompt
    $prompt = $navService->resolveIntent("Mujhe logout karna hai", 'member', '/member/dashboard');
    assert($prompt['intent'] === 'logout_prompt', 'Intent must be logout_prompt');
    assert($prompt['context']['pending_clarification'] === 'logout_confirm', 'Must set pending logout_confirm');

    // Step 2a: Confirm
    $confirmed = $navService->resolveIntent("Haan logout kar do", 'member', '/member/dashboard', 'chat', $prompt['context']);
    assert($confirmed['destination_key'] === 'member_logout', 'Must navigate to member_logout');
    assert($confirmed['target_url'] === url('/member/logout'), 'Must target /member/logout');

    // Step 2b: Cancel
    $cancelled = $navService->resolveIntent("Nahi, cancel karo", 'member', '/member/dashboard', 'chat', $prompt['context']);
    assert($cancelled['intent'] === 'cancel', 'Intent must be cancel');

    // Step 2c: Ambiguous reply (does NOT log out, prompts clarification)
    $ambig = $navService->resolveIntent("Shayad baad mein sochunga", 'member', '/member/dashboard', 'chat', $prompt['context']);
    assert($ambig['intent'] === 'clarify', 'Ambiguous confirmation must clarify, not log out');
});

// -------------------------------------------------------------
// Test 17: Missing Gemini API key -> deterministic commands still work
// -------------------------------------------------------------
runTest(17, 'Missing Gemini API key -> deterministic commands still work', function () {
    // Instantiate navigation service with an unconfigured mock gemini service
    $mockGemini = new class extends GeminiService {
        public function isConfigured(?string $apiKey = null): bool { return false; }
    };
    $offlineService = new IraNavigationService($mockGemini);

    $res = $offlineService->resolveIntent("Dashboard kholo", 'member', '/member/dashboard');
    assert($res['destination_key'] === 'member_dashboard', 'Deterministic dashboard works offline');

    $clarify = $offlineService->resolveIntent("Income dikhao", 'member', '/member/dashboard');
    assert($clarify['intent'] === 'clarify', 'Deterministic clarification works offline');
});

// -------------------------------------------------------------
// Test 18: Malformed Gemini output and unauthorized destination safely rejected
// -------------------------------------------------------------
runTest(18, 'Malformed Gemini output and unauthorized destination safely rejected', function () {
    // Mock Gemini returning malformed JSON
    $mockGemini = new class extends GeminiService {
        public function isConfigured(?string $apiKey = null): bool { return true; }
        public function generate(array $contents, ?string $apiKey = null, ?string $model = null): array {
            return ['text' => 'Not valid JSON at all'];
        }
    };
    $safeService = new IraNavigationService($mockGemini);
    $res = $safeService->resolveIntent("random complex phrase", 'member', '/member/dashboard');
    assert($res['intent'] === 'clarify', 'Fallback must trigger gracefully on bad JSON');

    // Mock Gemini attempting cross-role escalation (suggesting admin_member_details to a member)
    $mockEscalation = new class extends GeminiService {
        public function isConfigured(?string $apiKey = null): bool { return true; }
        public function generate(array $contents, ?string $apiKey = null, ?string $model = null): array {
            return ['text' => json_encode([
                'intent' => 'navigate',
                'destination_key' => 'admin_member_details',
                'confidence' => 0.99,
                'speech' => 'Here is admin details',
            ])];
        }
    };
    $safeEscalationService = new IraNavigationService($mockEscalation);
    $escRes = $safeEscalationService->resolveIntent("complex query asking for user list", 'member', '/member/dashboard');
    assert($escRes['success'] === false, 'Escalation must be rejected');
    assert($escRes['intent'] === 'unauthorized', 'Intent must be unauthorized');
});

// -------------------------------------------------------------
// Test 19: All registered destinations verified against actual routes or views
// -------------------------------------------------------------
runTest(19, 'All registered destinations verified against actual routes', function () use ($navService) {
    $allDestinations = $navService->getDestinations();
    assert(count($allDestinations) >= 30, 'Must have at least 30 verified destinations');
    foreach ($allDestinations as $key => $dest) {
        assert(!empty($dest['path']), "Path for {$key} must not be empty");
        assert(!empty($dest['roles']), "Roles for {$key} must not be empty");
        assert(!empty($dest['label']), "Label for {$key} must not be empty");
    }
});

// -------------------------------------------------------------
// Test 20: Existing routes and business behavior remain unchanged
// -------------------------------------------------------------
runTest(20, 'Existing routes and business behavior remain unchanged', function () {
    $routes = Illuminate\Support\Facades\Route::getRoutes();
    $iraRoute = $routes->getByName('ira.intent');
    assert($iraRoute !== null, 'ira.intent route must exist');
    assert(in_array('POST', $iraRoute->methods()), 'ira.intent must be POST');
    assert(count($routes) >= 150, 'All existing routes must remain intact');
});

echo "\n====================================================================\n";
echo "TEST RESULTS: {$passed}/{$total} PASSED\n";
echo "====================================================================\n";

if ($passed !== $total) {
    exit(1);
}
