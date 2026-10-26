<?php

namespace Tests\Feature;

use App\Services\AI\AppNavigationOntology;
use App\Services\AI\IraNavigationService;
use Tests\TestCase;

class IraNavigationRegressionTest extends TestCase
{
    protected IraNavigationService $iraService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->iraService = app(IraNavigationService::class);
    }

    /**
     * Requirement 1: All 55 canonical destinations must map to registered Laravel routes.
     */
    public function test_canonical_destinations_route_consistency(): void
    {
        $errors = AppNavigationOntology::validateConsistency();
        $this->assertEmpty($errors, 'Route consistency errors detected: ' . implode(', ', $errors));
    }

    /**
     * Requirement 2: Member section discovery in Hinglish.
     */
    public function test_member_section_discovery_hinglish(): void
    {
        $testCases = [
            'Earning section mein kaun-kaun se pages hain?' => 'earnings',
            'Add fund section mein kya hai?' => 'add_fund',
            'Withdrawal section ke pages batao' => 'withdrawal_section',
            'Staking section ke baare mein batao' => 'investment',
            'Team management mein kitne pages hain' => 'team_management',
            'P2P section dikhao' => 'p2p',
            'Support section ke pages dikhao' => 'support',
        ];

        foreach ($testCases as $utterance => $expectedSectionKey) {
            $result = $this->iraService->resolveIntent($utterance, 'member');

            $this->assertTrue($result['success'], "Failed for utterance: {$utterance}");
            $this->assertEquals('clarify', $result['intent'], "Intent should be 'clarify' for section discovery: {$utterance}");
            $this->assertArrayHasKey('section_discovery', $result, "Missing section_discovery for: {$utterance}");
            $this->assertEquals($expectedSectionKey, $result['section_discovery']['section_key'], "Wrong section matched for: {$utterance}");
            $this->assertNotEmpty($result['section_discovery']['pages'], "Pages list should not be empty for: {$utterance}");
            $this->assertEquals('section_page_choice', $result['context']['pending_clarification'] ?? null);
        }
    }

    /**
     * Requirement 3: Member section discovery with Devanagari Hindi transcript inputs.
     */
    public function test_member_section_discovery_devanagari_hindi(): void
    {
        $testCases = [
            'अर्निंग सेक्शन में कौन-कौन से पेज हैं?' => 'earnings',
            'ऐड फंड में क्या-क्या है?' => 'add_fund',
            'विथड्रॉल सेक्शन दिखाओ' => 'withdrawal_section',
            'स्टेकिंग सेक्शन के बारे में बताओ' => 'investment',
            'टीम मैनेजमेंट में कौन से पेज हैं' => 'team_management',
            'सपोर्ट सेक्शन में क्या-क्या पेज हैं' => 'support',
        ];

        foreach ($testCases as $utterance => $expectedSectionKey) {
            $result = $this->iraService->resolveIntent($utterance, 'member');

            $this->assertTrue($result['success'], "Failed for Devanagari utterance: {$utterance}");
            $this->assertEquals('clarify', $result['intent']);
            $this->assertArrayHasKey('section_discovery', $result);
            $this->assertEquals($expectedSectionKey, $result['section_discovery']['section_key']);
            $this->assertNotEmpty($result['section_discovery']['pages']);
            $this->assertEquals('section_page_choice', $result['context']['pending_clarification'] ?? null);
        }
    }

    /**
     * Requirement 4: Admin section discovery.
     */
    public function test_admin_section_discovery(): void
    {
        $testCases = [
            'Member management section mein kaunse page hain' => 'member_management',
            'Funds management section ke pages batao' => 'funds_management',
            'Payment management ke pages dikhao' => 'payment_management',
            'Admin staking section mein kya hai' => 'staking_section',
            'Income management section ke saare pages' => 'income_section',
            'Support tickets section mein kaunse pages hain' => 'support',
        ];

        foreach ($testCases as $utterance => $expectedSectionKey) {
            $result = $this->iraService->resolveIntent($utterance, 'admin');

            $this->assertTrue($result['success'], "Failed for Admin utterance: {$utterance}");
            $this->assertEquals('clarify', $result['intent']);
            $this->assertArrayHasKey('section_discovery', $result);
            $this->assertEquals($expectedSectionKey, $result['section_discovery']['section_key']);
            $this->assertNotEmpty($result['section_discovery']['pages']);
        }
    }

    /**
     * Requirement 5: Multi-turn ordinal selection following section discovery.
     */
    public function test_multi_turn_ordinal_selection(): void
    {
        $context = [
            'pending_clarification' => 'section_page_choice',
            'pending_section' => 'earnings',
            'pending_options' => [
                'Staking Income' => 'member_income_staking',
                'Level Income' => 'member_income_level',
                'Direct Income' => 'member_income_direct',
                'Reward Income' => 'member_income_reward',
                'Reward Details' => 'member_income_reward_details',
            ],
        ];

        $ordinals = [
            'Pehla wala' => 'member_income_staking',
            'Doosra wala' => 'member_income_level',
            'Teesra wala' => 'member_income_direct',
            'Chautha wala' => 'member_income_reward',
            'Panchva wala' => 'member_income_reward_details',
            '1st' => 'member_income_staking',
            '2nd' => 'member_income_level',
            'पहला' => 'member_income_staking',
            'दूसरा' => 'member_income_level',
            'तीसरा' => 'member_income_direct',
            'चौथा' => 'member_income_reward',
            'पांचवां' => 'member_income_reward_details',
        ];

        foreach ($ordinals as $utterance => $expectedDest) {
            $result = $this->iraService->resolveIntent($utterance, 'member', '', 'chat', $context);

            $this->assertTrue($result['success'], "Failed for ordinal: {$utterance}");
            $this->assertEquals('navigate', $result['intent'], "Intent should be 'navigate' for: {$utterance}");
            $this->assertEquals($expectedDest, $result['destination_key'], "Wrong destination for: {$utterance}");
            $this->assertNotNull($result['target_url']);
        }
    }

    /**
     * Requirement 6: Correction "Nahi, doosra page" must navigate to option 2 and NOT cancel.
     */
    public function test_multi_turn_selection_with_correction(): void
    {
        $context = [
            'pending_clarification' => 'section_page_choice',
            'pending_section' => 'earnings',
            'pending_options' => [
                'Staking Income' => 'member_income_staking',
                'Level Income' => 'member_income_level',
                'Direct Income' => 'member_income_direct',
            ],
        ];

        $result = $this->iraService->resolveIntent('Nahi, doosra page', 'member', '', 'chat', $context);
        $this->assertTrue($result['success']);
        $this->assertEquals('navigate', $result['intent']);
        $this->assertEquals('member_income_level', $result['destination_key']);
    }

    /**
     * Requirement 7: Explicit cancellation cleanly terminates flow and clears context.
     */
    public function test_multi_turn_explicit_cancel(): void
    {
        $context = [
            'pending_clarification' => 'section_page_choice',
            'pending_section' => 'earnings',
            'pending_options' => [
                'Staking Income' => 'member_income_staking',
                'Level Income' => 'member_income_level',
            ],
        ];

        $cancelUtterances = ['Cancel kar do', 'Nahi, rehne do', 'Ruk jao'];

        foreach ($cancelUtterances as $utterance) {
            $result = $this->iraService->resolveIntent($utterance, 'member', '', 'chat', $context);
            $this->assertTrue($result['success']);
            $this->assertEquals('cancel', $result['intent']);
            $this->assertEmpty($result['context']);
        }
    }

    /**
     * Requirement 8: Unrelated navigation command during pending clarification falls through to normal navigation.
     */
    public function test_multi_turn_fallthrough_to_unrelated_command(): void
    {
        $context = [
            'pending_clarification' => 'section_page_choice',
            'pending_section' => 'earnings',
            'pending_options' => [
                'Staking Income' => 'member_income_staking',
                'Level Income' => 'member_income_level',
            ],
        ];

        $result = $this->iraService->resolveIntent('Dashboard kholo', 'member', '', 'chat', $context);
        $this->assertTrue($result['success']);
        $this->assertEquals('navigate', $result['intent']);
        $this->assertEquals('member_dashboard', $result['destination_key']);
    }

    /**
     * Requirement 9: Strict Role Isolation and Server-Authoritative Cross-Portal Blocking.
     */
    public function test_role_isolation_member_cannot_access_admin_portal(): void
    {
        // Member asks for admin section or admin page
        $queries = [
            'Admin Dashboard kholo',
            'Member Management section dikhao',
            'Members Details page open karo',
            'Funds Management section mein kya hai',
            'Account control page kholo',
        ];

        foreach ($queries as $utterance) {
            $result = $this->iraService->resolveIntent($utterance, 'member');
            $this->assertFalse($result['success'], "Should fail authorization for: {$utterance}");
            $this->assertEquals('unauthorized', $result['intent'], "Intent must be 'unauthorized' for: {$utterance}");
            $this->assertNull($result['target_url'], "target_url must be null for unauthorized query: {$utterance}");
        }
    }

    /**
     * Requirement 10: Guest cannot access Member or Admin without login.
     */
    public function test_role_isolation_guest_redirects_to_login(): void
    {
        $memberResult = $this->iraService->resolveIntent('Dashboard kholo', 'guest');
        $this->assertFalse($memberResult['success']);
        $this->assertEquals('unauthorized', $memberResult['intent']);
        $this->assertStringContainsString('/member', $memberResult['target_url']);

        $adminResult = $this->iraService->resolveIntent('Admin dashboard kholo', 'guest');
        $this->assertFalse($adminResult['success']);
        $this->assertEquals('unauthorized', $adminResult['intent']);
        $this->assertStringContainsString('/admin', $adminResult['target_url']);
    }

    /**
     * Requirement 11: Admin dashboard messages not confused with dashboard.
     */
    public function test_admin_dashboard_messages_not_confused_with_dashboard(): void
    {
        $msgResult = $this->iraService->resolveIntent('Dashboard Messages kholo', 'admin');
        $this->assertTrue($msgResult['success']);
        $this->assertEquals('admin_dash_messages', $msgResult['destination_key']);
        $this->assertStringContainsString('/admin/dash-messages', $msgResult['target_url']);

        $popupResult = $this->iraService->resolveIntent('Popup Image page open karo', 'admin');
        $this->assertTrue($popupResult['success']);
        $this->assertEquals('admin_popup_image', $popupResult['destination_key']);
        $this->assertStringContainsString('/admin/popup-image', $popupResult['target_url']);

        $dashResult = $this->iraService->resolveIntent('Admin Dashboard kholo', 'admin');
        $this->assertTrue($dashResult['success']);
        $this->assertEquals('admin_dashboard', $dashResult['destination_key']);
        $this->assertStringContainsString('/admin/dashboard', $dashResult['target_url']);
    }

    /**
     * Requirement 12: Section discovery precedence over ambiguous intent handlers.
     */
    public function test_section_discovery_precedence_over_ambiguous_intent(): void
    {
        // Singular "kaunse page hai" should trigger section discovery, not ambiguous income clarification
        $result = $this->iraService->resolveIntent('Earning section mein kaunse page hai', 'member');
        $this->assertTrue($result['success']);
        $this->assertEquals('clarify', $result['intent']);
        $this->assertArrayHasKey('section_discovery', $result);
        $this->assertEquals('earnings', $result['section_discovery']['section_key']);
    }

    /**
     * Requirement 13: Full HTTP pipeline end-to-end integration via POST /ira/intent.
     */
    public function test_http_endpoint_member_session_flow(): void
    {
        // 1. Ask about section as Member
        $response = $this->withSession([
            'MEMBER_LOGIN' => true,
            'MEMBER_ID' => 1,
            'name' => 'Test Member',
        ])->postJson('/ira/intent', [
            'speech' => 'Earning section mein kaun-kaun se pages hain?',
            'current_page' => '/member/dashboard',
            'current_mode' => 'chat',
            'context' => [],
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('clarify', $data['intent']);
        $this->assertArrayHasKey('section_discovery', $data);
        $this->assertEquals('earnings', $data['section_discovery']['section_key']);

        // 2. Select option via ordinal in follow-up
        $followUp = $this->withSession([
            'MEMBER_LOGIN' => true,
            'MEMBER_ID' => 1,
            'name' => 'Test Member',
        ])->postJson('/ira/intent', [
            'speech' => 'Pehla wala',
            'current_page' => '/member/dashboard',
            'current_mode' => 'chat',
            'context' => $data['context'],
        ]);

        $followUp->assertStatus(200);
        $followData = $followUp->json();
        $this->assertEquals('navigate', $followData['intent']);
        $this->assertEquals('member_income_staking', $followData['destination_key']);
        $this->assertStringContainsString('/member/income/staking-incomes', $followData['target_url']);
    }

    /**
     * Requirement 14: HTTP endpoint blocks unauthorized admin requests from Member.
     */
    public function test_http_endpoint_member_blocked_from_admin_destination(): void
    {
        $response = $this->withSession([
            'MEMBER_LOGIN' => true,
            'MEMBER_ID' => 1,
            'name' => 'Test Member',
        ])->postJson('/ira/intent', [
            'speech' => 'Admin dashboard kholo',
            'current_page' => '/member/dashboard',
            'current_mode' => 'chat',
            'context' => [],
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('unauthorized', $data['intent']);
        $this->assertNull($data['target_url']);
    }

    /**
     * Requirement 15: Browser History & Assistant Window Commands.
     */
    public function test_browser_history_and_assistant_window_commands(): void
    {
        $backResult = $this->iraService->resolveIntent('Pichhe jao', 'member');
        $this->assertTrue($backResult['success']);
        $this->assertEquals('go_back', $backResult['intent']);

        $forwardResult = $this->iraService->resolveIntent('Aage jao', 'member');
        $this->assertTrue($forwardResult['success']);
        $this->assertEquals('go_forward', $forwardResult['intent']);

        $closeResult = $this->iraService->resolveIntent('Assistant band karo', 'member');
        $this->assertTrue($closeResult['success']);
        $this->assertEquals('close_assistant', $closeResult['intent']);
    }

    /**
     * Requirement 16: Two-step secure logout confirmation flow.
     */
    public function test_two_step_logout_confirmation_flow(): void
    {
        // 1. Initial logout command prompts for confirmation
        $promptResult = $this->iraService->resolveIntent('Logout kar do', 'member');
        $this->assertTrue($promptResult['success']);
        $this->assertEquals('logout_prompt', $promptResult['intent']);
        $this->assertEquals('logout_confirm', $promptResult['context']['pending_clarification'] ?? null);

        // 2. Answering 'Haan' completes logout navigation
        $confirmResult = $this->iraService->resolveIntent('Haan', 'member', '', 'chat', $promptResult['context']);
        $this->assertTrue($confirmResult['success']);
        $this->assertEquals('navigate', $confirmResult['intent']);
        $this->assertEquals('member_logout', $confirmResult['destination_key']);
        $this->assertStringContainsString('/member/logout', $confirmResult['target_url']);

        // 3. Answering 'Nahi' cancels logout
        $cancelResult = $this->iraService->resolveIntent('Nahi, mat karo', 'member', '', 'chat', $promptResult['context']);
        $this->assertTrue($cancelResult['success']);
        $this->assertEquals('cancel', $cancelResult['intent']);
    }

    /**
     * Requirement 17: Public frontend destinations.
     */
    public function test_public_frontend_destinations(): void
    {
        $homeResult = $this->iraService->resolveIntent('Home page par chalo', 'guest');
        $this->assertTrue($homeResult['success']);
        $this->assertEquals('home', $homeResult['destination_key']);

        $galleryResult = $this->iraService->resolveIntent('Gallery dikhao', 'guest');
        $this->assertTrue($galleryResult['success']);
        $this->assertEquals('gallery', $galleryResult['destination_key']);
    }

    /**
     * Requirement 18: Child page named inside parent section query navigates immediately.
     */
    public function test_child_page_specified_inside_section_query(): void
    {
        $result = $this->iraService->resolveIntent('Earnings section mein Direct Income kholo', 'member');
        $this->assertTrue($result['success']);
        $this->assertEquals('navigate', $result['intent']);
        $this->assertEquals('member_income_direct', $result['destination_key']);
    }

    /**
     * Requirement 19: Phonetic 'petrol' speech recognition alias resolves to P2P.
     */
    public function test_phonetic_petrol_alias_resolves_to_p2p(): void
    {
        $result = $this->iraService->resolveIntent('Petrol wallet kholo', 'member');
        $this->assertTrue($result['success']);
        $this->assertEquals('navigate', $result['intent']);
        $this->assertEquals('member_p2p_wallet', $result['destination_key']);
    }

    /**
     * Phase 3 Verification: P2P open command resolves to member P2P wallet.
     */
    public function test_phase3_p2p_open_phrase_resolves_to_p2p_wallet(): void
    {
        $result = $this->iraService->resolveIntent('P2P open karna hai', 'member');
        $this->assertTrue($result['success']);
        $this->assertEquals('navigate', $result['intent']);
        $this->assertEquals('member_p2p_wallet', $result['destination_key']);
    }

    /**
     * Phase 3 Verification: Explicit section request takes precedence over shorter page substring.
     */
    public function test_phase3_withdrawal_section_open_resolves_to_section_discovery(): void
    {
        $result = $this->iraService->resolveIntent('open the withdrawal section', 'member');
        $this->assertTrue($result['success']);
        $this->assertEquals('clarify', $result['intent']);
        $this->assertTrue($result['needs_clarification']);
        $this->assertEquals('withdrawal_section', $result['section_discovery']['section_key']);
    }

    /**
     * Phase 3 Verification: Multi-turn section selection follow-up navigates accurately.
     */
    public function test_phase3_multiturn_section_followup_navigation(): void
    {
        $t1 = $this->iraService->resolveIntent('earning section dikhao', 'member');
        $this->assertEquals('clarify', $t1['intent']);
        $this->assertEquals('earnings', $t1['section_discovery']['section_key']);

        $t2 = $this->iraService->resolveIntent('first option', 'member', '/member/dashboard', 'voice', $t1['context']);
        $this->assertTrue($t2['success']);
        $this->assertEquals('navigate', $t2['intent']);
        $this->assertEquals('member_income_staking', $t2['destination_key']);
    }
}
