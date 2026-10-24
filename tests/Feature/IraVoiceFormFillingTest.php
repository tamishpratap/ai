<?php

namespace Tests\Feature;

use App\Services\AI\IraFormRegistry;
use App\Services\AI\IraFormUnderstandingService;
use App\Services\AI\IraNavigationService;
use Tests\TestCase;

class IraVoiceFormFillingTest extends TestCase
{
    protected IraFormUnderstandingService $formAi;
    protected IraNavigationService $navService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->formAi = app(IraFormUnderstandingService::class);
        $this->navService = app(IraNavigationService::class);
    }

    /**
     * Test 1: Registry integrity and coverage across all portals
     */
    public function test_registry_integrity_and_coverage(): void
    {
        $forms = IraFormRegistry::getAllForms();

        $this->assertNotEmpty($forms);
        $this->assertArrayHasKey('member_p2p_transfer', $forms);
        $this->assertArrayHasKey('member_staking', $forms);
        $this->assertArrayHasKey('member_fund_import', $forms);
        $this->assertArrayHasKey('member_wallet_withdrawal', $forms);
        $this->assertArrayHasKey('member_support_create', $forms);
        $this->assertArrayHasKey('admin_funds_add', $forms);
        $this->assertArrayHasKey('admin_funds_deduct', $forms);
        $this->assertArrayHasKey('admin_set_rates', $forms);
        $this->assertArrayHasKey('admin_date_filter', $forms);
        $this->assertArrayHasKey('public_member_signup', $forms);
        $this->assertArrayHasKey('public_member_signin', $forms);
        $this->assertArrayHasKey('public_admin_login', $forms);
        $this->assertArrayHasKey('public_member_forget_password', $forms);
        $this->assertArrayHasKey('admin_profile_email', $forms);
        $this->assertArrayHasKey('member_security_email', $forms);

        foreach ($forms as $key => $schema) {
            $this->assertNotEmpty($schema['title'], "Form {$key} must have a title");
            $this->assertNotEmpty($schema['selector'], "Form {$key} must have a selector");
            $this->assertNotEmpty($schema['roles'], "Form {$key} must specify authorized roles");
            $this->assertNotEmpty($schema['fields'], "Form {$key} must specify fields");

            foreach ($schema['fields'] as $fKey => $fDef) {
                $this->assertNotEmpty($fDef['name'], "Field {$fKey} in {$key} must have a name attribute");
                $this->assertNotEmpty($fDef['label'], "Field {$fKey} in {$key} must have a visible label");
                $this->assertNotEmpty($fDef['type'], "Field {$fKey} in {$key} must have an input type");
            }
        }
    }

    /**
     * Test 2: Role Authorization Restrictions
     */
    public function test_role_authorization_restrictions(): void
    {
        // Member form
        $this->assertTrue(IraFormRegistry::isFormAuthorized('member_p2p_transfer', 'member'));
        $this->assertFalse(IraFormRegistry::isFormAuthorized('member_p2p_transfer', 'guest'));
        $this->assertFalse(IraFormRegistry::isFormAuthorized('member_p2p_transfer', 'admin'));

        // Admin form
        $this->assertTrue(IraFormRegistry::isFormAuthorized('admin_funds_add', 'admin'));
        $this->assertFalse(IraFormRegistry::isFormAuthorized('admin_funds_add', 'member'));
        $this->assertFalse(IraFormRegistry::isFormAuthorized('admin_funds_add', 'guest'));

        // Public form
        $this->assertTrue(IraFormRegistry::isFormAuthorized('public_member_signup', 'guest'));
        $this->assertTrue(IraFormRegistry::isFormAuthorized('public_member_signup', 'member'));
        $this->assertTrue(IraFormRegistry::isFormAuthorized('public_member_signup', 'admin'));
    }

    /**
     * Test 3: Path Matching for Forms on Page
     */
    public function test_find_forms_by_page_path(): void
    {
        $memberForms = IraFormRegistry::findFormsByPath('/member/p2p-wallet', 'member');
        $this->assertArrayHasKey('member_p2p_transfer', $memberForms);

        $stakingForms = IraFormRegistry::findFormsByPath('/member/staking', 'member');
        $this->assertArrayHasKey('member_staking', $stakingForms);

        $adminForms = IraFormRegistry::findFormsByPath('/admin/funds/add-funds', 'admin');
        $this->assertArrayHasKey('admin_funds_add', $adminForms);

        // Wildcard admin path matches admin date filter
        $filterForms = IraFormRegistry::findFormsByPath('/admin/new-withdrawal-request', 'admin');
        $this->assertArrayHasKey('admin_date_filter', $filterForms);
    }

    /**
     * Test 4: Single Field Extraction (Text and Numeric)
     */
    public function test_single_field_extraction(): void
    {
        // 1. P2P Amount
        $res1 = $this->formAi->processFormTurn('Amount mein 1500 daalo', 'member_p2p_transfer', 'member');
        $this->assertTrue($res1['success']);
        $this->assertCount(1, $res1['form_updates']);
        $this->assertEquals('amount', $res1['form_updates'][0]['field']);
        $this->assertEquals(1500, $res1['form_updates'][0]['value']);

        // 2. Receiver Member ID (continuous)
        $res2 = $this->formAi->processFormTurn('Member ID GAB10025 hai', 'member_p2p_transfer', 'member');
        $this->assertTrue($res2['success']);
        $this->assertCount(1, $res2['form_updates']);
        $this->assertEquals('memberid', $res2['form_updates'][0]['field']);
        $this->assertEquals('GAB10025', $res2['form_updates'][0]['value']);

        // 3. Receiver Member ID (spaced letters)
        $res3 = $this->formAi->processFormTurn('G A B 9 8 7 6 5 ko transfer karna hai', 'member_p2p_transfer', 'member');
        $this->assertTrue($res3['success']);
        $this->assertCount(1, $res3['form_updates']);
        $this->assertEquals('GAB98765', $res3['form_updates'][0]['value']);

        // 4. Support Subject
        $res4 = $this->formAi->processFormTurn('Subject mein Account verification likho', 'member_support_create', 'member');
        $this->assertTrue($res4['success']);
        $this->assertCount(1, $res4['form_updates']);
        $this->assertEquals('subject', $res4['form_updates'][0]['field']);
        $this->assertStringContainsString('Account verification', $res4['form_updates'][0]['value']);
    }

    /**
     * Test 5: Multi-Field Extraction in a Single Turn
     */
    public function test_multi_field_extraction_in_single_turn(): void
    {
        // Utterance with both recipient and amount
        $speech = 'GAB10045 ko 500 dollars transfer kar do';
        $res = $this->formAi->processFormTurn($speech, 'member_p2p_transfer', 'member');

        $this->assertTrue($res['success']);
        $this->assertCount(2, $res['form_updates']);

        $updatesByField = [];
        foreach ($res['form_updates'] as $u) {
            $updatesByField[$u['field']] = $u['value'];
        }

        $this->assertEquals('GAB10045', $updatesByField['memberid']);
        $this->assertEquals(500, $updatesByField['amount']);
        $this->assertStringContainsString('GAB10045', $res['speech']);
        $this->assertStringContainsString('500', $res['speech']);
    }

    /**
     * Test 6: Dropdown Options Fuzzy Resolution and Selection
     */
    public function test_dropdown_options_resolution(): void
    {
        // 1. Staking package: Gold
        $res1 = $this->formAi->processFormTurn('Gold package select karo', 'member_staking', 'member');
        $this->assertTrue($res1['success']);
        $this->assertEquals('stak_package', $res1['form_updates'][0]['field']);
        $this->assertEquals('Gold', $res1['form_updates'][0]['value']);

        // 2. Staking package: Silver in Hinglish
        $res2 = $this->formAi->processFormTurn('Silver plan chuno', 'member_staking', 'member');
        $this->assertTrue($res2['success']);
        $this->assertEquals('Silver', $res2['form_updates'][0]['value']);

        // 3. Admin Funds Add: Wallet Type (P2P Wallet -> Fund Wallet)
        $res3 = $this->formAi->processFormTurn('Wallet type P2P wallet rakho', 'admin_funds_add', 'admin');
        $this->assertTrue($res3['success']);
        $this->assertEquals('wallet_type', $res3['form_updates'][0]['field']);
        $this->assertEquals('Fund Wallet', $res3['form_updates'][0]['value']);

        // 4. Admin Date Filter: Preset (This month)
        $res4 = $this->formAi->processFormTurn('Preset mein this month select karo', 'admin_date_filter', 'admin');
        $this->assertTrue($res4['success']);
        $this->assertEquals('preset', $res4['form_updates'][0]['field']);
        $this->assertEquals('this_month', $res4['form_updates'][0]['value']);
    }

    /**
     * Test 7: Hindi and Devanagari Spoken Number Parsing
     */
    public function test_hindi_spoken_numbers_parsing(): void
    {
        // "pachas" -> 50
        $res1 = $this->formAi->processFormTurn('Amount pachas dollars bhar do', 'member_p2p_transfer', 'member');
        $this->assertTrue($res1['success']);
        $this->assertEquals(50, $res1['form_updates'][0]['value']);

        // "dedh sau" -> 150
        $res2 = $this->formAi->processFormTurn('Amount mein dedh sau daalo', 'member_p2p_transfer', 'member');
        $this->assertTrue($res2['success']);
        $this->assertEquals(150, $res2['form_updates'][0]['value']);

        // "do hazaar" -> 2000
        $res3 = $this->formAi->processFormTurn('Amount do hazaar rakho', 'member_p2p_transfer', 'member');
        $this->assertTrue($res3['success']);
        $this->assertEquals(2000, $res3['form_updates'][0]['value']);

        // Devanagari Hindi numerals: "अमाउंट 500"
        $res4 = $this->formAi->processFormTurn('अमाउंट ५०० डॉलर कर दो', 'member_p2p_transfer', 'member');
        $this->assertTrue($res4['success']);
        $this->assertEquals(500, $res4['form_updates'][0]['value']);
    }

    /**
     * Test 8: Staking Package Combined with Amount Limits Check
     */
    public function test_staking_package_limit_validation(): void
    {
        // Gold package is $1,100 to $5,000. If user says $500 with Gold, warn!
        $speech = 'Gold package chuno aur amount 500 bhar do';
        $res = $this->formAi->processFormTurn($speech, 'member_staking', 'member');

        $this->assertTrue($res['success']);
        $this->assertNotEmpty($res['warnings']);
        $this->assertStringContainsString('Gold package ke liye minimum amount $1100', $res['warnings'][0]);
        $this->assertStringContainsString('minimum amount $1100', $res['speech']);

        // Valid amount within Gold limits ($2000) produces no limit warning
        $speechValid = 'Gold package chuno aur amount 2000 bhar do';
        $resValid = $this->formAi->processFormTurn($speechValid, 'member_staking', 'member');
        $this->assertTrue($resValid['success']);
        $this->assertEmpty($resValid['warnings']);
    }

    /**
     * Test 9: Strict Sensitive Field Protection (Passwords/PINs are NEVER populated)
     */
    public function test_sensitive_fields_never_populated(): void
    {
        // User asking to fill transaction password
        $res = $this->formAi->processFormTurn('Transaction password secret123 daal do', 'member_p2p_transfer', 'member');

        // Form updates must be EMPTY
        $this->assertEmpty($res['form_updates']);
        $this->assertNotEmpty($res['sensitive_skipped']);
        $this->assertEquals('sensitive_field_blocked', $res['status']);
        $this->assertStringContainsString('manually enter', $res['speech']);

        // Even in a multi-field turn, password is stripped and ordinary fields are kept
        $multiSpeech = 'Amount 500 daalo aur transaction password 123456 bhar do';
        $resMulti = $this->formAi->processFormTurn($multiSpeech, 'member_p2p_transfer', 'member');

        $this->assertTrue($resMulti['success']);
        $this->assertCount(1, $resMulti['form_updates']); // Only amount!
        $this->assertEquals('amount', $resMulti['form_updates'][0]['field']);
        $this->assertNotEmpty($resMulti['sensitive_skipped']);
        $this->assertStringContainsString('manually enter', $resMulti['speech']);
    }

    /**
     * Test 10: File Inputs Require Manual Browser Interaction
     */
    public function test_file_inputs_instruct_manual_selection(): void
    {
        $res = $this->formAi->processFormTurn('Profile image photo upload kar do', 'member_profile_user_summary', 'member');

        $this->assertEmpty($res['form_updates']);
        $this->assertEquals('file_input_blocked', $res['status']);
        $this->assertStringContainsString('Choose File', $res['speech']);
    }

    /**
     * Test 11: Read-Only Fields Cannot Be Overwritten
     */
    public function test_read_only_fields_explanation(): void
    {
        $res = $this->formAi->processFormTurn('Member ID badal kar GAB99999 kar do', 'member_staking', 'member');

        $this->assertEmpty($res['form_updates']);
        $this->assertEquals('readonly_field', $res['status']);
        $this->assertStringContainsString('read-only', $res['speech']);
    }

    /**
     * Test 12: Remaining Fields Query
     */
    public function test_remaining_fields_query(): void
    {
        // When form is completely empty
        $resEmpty = $this->formAi->processFormTurn('Form mein aur kya bharna baaki hai?', 'member_p2p_transfer', 'member', []);
        $this->assertTrue($resEmpty['success']);
        $this->assertEquals('status_query', $resEmpty['status']);
        $this->assertContains('Receiver Member ID', $resEmpty['remaining_required']);
        $this->assertContains('Transfer Amount ($)', $resEmpty['remaining_required']);

        // When memberid is already populated in DOM
        $resPartial = $this->formAi->processFormTurn(
            'Aur kya bharna hai?',
            'member_p2p_transfer',
            'member',
            ['memberid' => 'GAB10025']
        );
        $this->assertNotContains('Receiver Member ID', $resPartial['remaining_required']);
        $this->assertContains('Transfer Amount ($)', $resPartial['remaining_required']);
    }

    /**
     * Test 13: Reset / Clear Form Command
     */
    public function test_reset_form_command(): void
    {
        $res = $this->formAi->processFormTurn('Form clear karo', 'member_p2p_transfer', 'member');
        $this->assertTrue($res['success']);
        $this->assertEquals('form_reset', $res['status']);
        $this->assertNotEmpty($res['form_updates']);

        // Updates instruct setting empty string values
        foreach ($res['form_updates'] as $u) {
            $this->assertEquals('', $u['value']);
        }
        $this->assertStringContainsString('clear kar diye hain', $res['speech']);
    }

    /**
     * Test 14: HTTP Endpoint POST /ira/form-step Authorized Member Flow
     */
    public function test_http_endpoint_post_ira_form_step_member(): void
    {
        $response = $this->withSession(['MEMBER_LOGIN' => true, 'MEMBER_ID' => 'GAB10025'])
            ->postJson('/ira/form-step', [
                'form_key' => 'member_p2p_transfer',
                'speech' => 'Amount 750 dollars bhar do',
                'current_page' => '/member/p2p-wallet',
                'current_fields' => [],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'updated',
            'form_key' => 'member_p2p_transfer',
        ]);

        $data = $response->json();
        $this->assertCount(1, $data['form_updates']);
        $this->assertEquals(750, $data['form_updates'][0]['value']);
    }

    /**
     * Test 15: HTTP Endpoint Rejection for Unauthorized Role
     */
    public function test_http_endpoint_unauthorized_rejection(): void
    {
        // Guest user attempting to access protected member form
        $response = $this->postJson('/ira/form-step', [
            'form_key' => 'member_p2p_transfer',
            'speech' => 'Amount 1000 daalo',
            'current_page' => '/member/p2p-wallet',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'status' => 'unauthorized',
        ]);
    }

    /**
     * Test 16: HTTP Endpoint GET /ira/forms-for-page
     */
    public function test_http_endpoint_forms_for_page(): void
    {
        $response = $this->withSession(['MEMBER_LOGIN' => true, 'MEMBER_ID' => 'GAB10025'])
            ->getJson('/ira/forms-for-page?path=/member/staking');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'role' => 'member',
        ]);

        $data = $response->json();
        $this->assertArrayHasKey('member_staking', $data['forms']);
        $this->assertEquals('Create Investment / Staking', $data['forms']['member_staking']['title']);
    }

    /**
     * Test 17: Seamless Integration with POST /ira/intent on Form Pages
     */
    public function test_integration_with_ira_intent_endpoint(): void
    {
        // When user is on /member/staking and says "Gold package chuno aur amount 2500 rakho"
        $response = $this->withSession(['MEMBER_LOGIN' => true, 'MEMBER_ID' => 'GAB10025'])
            ->postJson('/ira/intent', [
                'speech' => 'Gold package chuno aur amount 2500 rakho',
                'current_page' => '/member/staking',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'intent' => 'form_fill',
            'form_key' => 'member_staking',
        ]);

        $data = $response->json();
        $this->assertCount(2, $data['form_updates']);
        $this->assertStringContainsString('Gold', $data['speech']);
        $this->assertStringContainsString('2500', $data['speech']);
    }

    /**
     * Test 18: No Automatic Business Mutation or Submissions
     */
    public function test_no_automatic_form_submission(): void
    {
        // Voice filling should only return field update instructions, NEVER trigger form submit
        $speech = 'Haan sab theek hai submit karo';
        $res = $this->formAi->processFormTurn($speech, 'member_p2p_transfer', 'member');

        // Form submission is deliberately NOT executed in Phase 7
        // Speech does not execute transaction
        $this->assertEmpty($res['form_updates']);
    }

    /**
     * Test 19: Phase 7 Hotfix - Main Wallet Withdrawal Form Filling Flow
     * User says: "Amount mein 100 daalo"
     * Result: Amount is populated with 100, transaction password remains blank, no submission occurs.
     */
    public function test_withdrawal_form_filling_flow(): void
    {
        $response = $this->withSession(['MEMBER_LOGIN' => true, 'MEMBER_ID' => 'GAB10025'])
            ->postJson('/ira/form-step', [
                'form_key' => 'member_wallet_withdrawal',
                'speech' => 'Amount mein 100 daalo',
                'current_page' => '/member/wallet',
                'current_fields' => [],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'updated',
            'form_key' => 'member_wallet_withdrawal',
        ]);

        $data = $response->json();
        $this->assertCount(1, $data['form_updates']);
        $this->assertEquals('amount', $data['form_updates'][0]['field']);
        $this->assertEquals(100, $data['form_updates'][0]['value']);
        $this->assertStringContainsString('Withdrawal Amount ($)', $data['speech']);
        $this->assertStringContainsString('100', $data['speech']);

        // Even if user mentions transaction password, it is strictly blocked
        $resSensitive = $this->withSession(['MEMBER_LOGIN' => true, 'MEMBER_ID' => 'GAB10025'])
            ->postJson('/ira/form-step', [
                'form_key' => 'member_wallet_withdrawal',
                'speech' => 'Amount 150 daalo aur password 123456 bhar do',
                'current_page' => '/member/wallet',
                'current_fields' => [],
            ]);

        $resSensitive->assertStatus(200);
        $dataSens = $resSensitive->json();
        $this->assertCount(1, $dataSens['form_updates']); // Only amount
        $this->assertEquals(150, $dataSens['form_updates'][0]['value']);
        $this->assertNotEmpty($dataSens['sensitive_skipped']);
        $this->assertStringContainsString('manually enter', $dataSens['speech']);
    }
}
