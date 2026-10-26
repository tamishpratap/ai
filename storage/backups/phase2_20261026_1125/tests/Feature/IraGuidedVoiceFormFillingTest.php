<?php

namespace Tests\Feature;

use App\Services\AI\IraFormRegistry;
use App\Services\AI\IraFormUnderstandingService;
use Tests\TestCase;

class IraGuidedVoiceFormFillingTest extends TestCase
{
    protected IraFormUnderstandingService $formAi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->formAi = app(IraFormUnderstandingService::class);
    }

    /**
     * Test 1: Complete Guided Flow across multiple turns (Step A -> B -> C -> D -> F)
     * Form: member_staking
     */
    public function test_complete_guided_flow_step_by_step(): void
    {
        // Turn 1: Initialization (Step A & Step B)
        $init = $this->formAi->initGuidedSession('member_staking', 'member');

        $this->assertTrue($init['success']);
        $this->assertEquals('asking_field', $init['status']);
        $this->assertEquals('prompt', $init['action']);
        $this->assertStringContainsString('Create Investment / Staking', $init['speech']);
        $this->assertStringContainsString('शुरू करते हैं', $init['speech']);
        $this->assertStringContainsString('Investment Package', $init['speech']);
        $this->assertEquals('stak_package', $init['guide_state']['current_field']);
        $this->assertEquals(['stak_package', 'amount'], $init['guide_state']['field_sequence']);
        $this->assertEquals('stak_package', $init['active_field']['key']);

        $guideState = $init['guide_state'];
        $currentFields = [];

        // Turn 2: User provides Package -> "Gold" (Step C & D)
        $turn2 = $this->formAi->processGuidedTurn('Gold', 'member_staking', 'member', $currentFields, $guideState);

        $this->assertTrue($turn2['success']);
        $this->assertEquals('field_updated', $turn2['status']);
        $this->assertEquals('prompt', $turn2['action']);
        $this->assertCount(1, $turn2['form_updates']);
        $this->assertEquals('stak_package', $turn2['form_updates'][0]['field']);
        $this->assertEquals('Gold', $turn2['form_updates'][0]['value']);
        $this->assertStringContainsString('Investment Package', $turn2['speech']);
        $this->assertStringContainsString('Gold', $turn2['speech']);
        $this->assertStringContainsString('select किया गया है', $turn2['speech']);
        $this->assertStringContainsString('Investment Amount', $turn2['speech']);
        $this->assertEquals('amount', $turn2['guide_state']['current_field']);
        $this->assertEquals('amount', $turn2['active_field']['key']);

        $guideState = $turn2['guide_state'];
        $currentFields['stak_package'] = 'Gold';

        // Turn 3: User provides Amount -> "1000" (Step F: Completion)
        $turn3 = $this->formAi->processGuidedTurn('1000', 'member_staking', 'member', $currentFields, $guideState);

        $this->assertTrue($turn3['success']);
        $this->assertEquals('completed', $turn3['status']);
        $this->assertEquals('complete', $turn3['action']);
        $this->assertCount(1, $turn3['form_updates']);
        $this->assertEquals('amount', $turn3['form_updates'][0]['field']);
        $this->assertEquals(1000, $turn3['form_updates'][0]['value']);
        $this->assertNull($turn3['guide_state']['current_field']);
        $this->assertEquals('completed', $turn3['guide_state']['status']);

        // Completion speech must instruct user regarding manual transaction password and manual submission
        $this->assertStringContainsString('Form के सभी आवश्यक fields भर दिए गए हैं', $turn3['speech']);
        $this->assertStringContainsString('Transaction Password', $turn3['speech']);
        $this->assertStringContainsString('manually', $turn3['speech']);
        $this->assertStringContainsString('Submit', $turn3['speech']);
    }

    /**
     * Test 2: Dropdown options resolution (named options and ordinal selection)
     */
    public function test_dropdown_options_resolution(): void
    {
        $schema = IraFormRegistry::getForm('member_staking');
        $init = $this->formAi->initGuidedSession('member_staking', 'member');
        $guideState = $init['guide_state'];

        // Ordinal selection: "dusra option" -> Gold
        $turnOrdinal = $this->formAi->processGuidedTurn('dusra option', 'member_staking', 'member', [], $guideState);
        $this->assertTrue($turnOrdinal['success']);
        $this->assertEquals('Gold', $turnOrdinal['form_updates'][0]['value']);

        // Direct option name: "Diamond"
        $turnDirect = $this->formAi->processGuidedTurn('Diamond', 'member_staking', 'member', [], $guideState);
        $this->assertTrue($turnDirect['success']);
        $this->assertEquals('Diamond', $turnDirect['form_updates'][0]['value']);
    }

    /**
     * Test 3: Dynamic lookup event verification
     */
    public function test_dynamic_lookup_event_configuration(): void
    {
        $schema = IraFormRegistry::getForm('member_p2p_transfer');
        $userIdField = $schema['fields']['memberid'];

        $this->assertContains('input', $userIdField['dynamic_events']);
        $this->assertContains('change', $userIdField['dynamic_events']);

        $init = $this->formAi->initGuidedSession('member_p2p_transfer', 'member');
        $turn = $this->formAi->processGuidedTurn('GAB10025', 'member_p2p_transfer', 'member', [], $init['guide_state']);

        $this->assertTrue($turn['success']);
        $this->assertEquals(['input', 'change'], $turn['form_updates'][0]['events']);
    }

    /**
     * Test 4: Multi-form page isolation (Security page with 2 forms)
     */
    public function test_multiform_page_isolation(): void
    {
        // Form 1: Transaction Password form
        $initTxn = $this->formAi->initGuidedSession('member_security_txn_password', 'member');
        $this->assertEquals('member_security_txn_password', $initTxn['form_key']);
        $this->assertEquals('form[action*="txnpassChange"]', $initTxn['form_selector']);
        $this->assertEquals('all_sensitive', $initTxn['status']);
        $this->assertStringContainsString('Change Transaction Password', $initTxn['speech']);

        // Form 2: Login Password form
        $initLogin = $this->formAi->initGuidedSession('member_security_login_password', 'member');
        $this->assertEquals('member_security_login_password', $initLogin['form_key']);
        $this->assertEquals('form[action*="passChange"]', $initLogin['form_selector']);
        $this->assertEquals('all_sensitive', $initLogin['status']);
        $this->assertStringContainsString('Change Account Password', $initLogin['speech']);
    }

    /**
     * Test 5: Sensitive-only form immediate manual-entry explanation
     */
    public function test_sensitive_only_form_immediate_explanation(): void
    {
        $init = $this->formAi->initGuidedSession('member_security_txn_password', 'member');

        $this->assertTrue($init['success']);
        $this->assertEquals('all_sensitive', $init['status']);
        $this->assertEquals('complete', $init['action']);
        $this->assertNull($init['active_field']);
        $this->assertEmpty($init['form_updates']);

        // Must explain in Hindi that password fields must be filled manually on the screen
        $this->assertStringContainsString('सुरक्षा के लिए कृपया password fields स्क्रीन पर manually भरें', $init['speech']);
        $this->assertStringContainsString('मैं इन fields में आवाज़ से password नहीं भरूँगी', $init['speech']);
    }

    /**
     * Test 6: Form with file input manual instruction
     */
    public function test_file_input_manual_instruction(): void
    {
        $schema = IraFormRegistry::getForm('member_profile_user_summary');
        $this->assertTrue($schema['fields']['profile_image']['file_upload']);

        $init = $this->formAi->initGuidedSession('member_profile_user_summary', 'member');
        $this->assertTrue($init['success']);

        // Complete the text fields sequence to verify file upload instruction
        $turn = $this->formAi->processGuidedTurn('India', 'member_profile_user_summary', 'member', ['name' => 'John', 'mobile' => '9876543210'], [
            'current_field' => 'country',
            'field_sequence' => ['name', 'mobile', 'country'],
            'filled_fields' => ['name', 'mobile'],
            'status' => 'in_progress',
        ]);
        $this->assertTrue($turn['success']);
        $this->assertEquals('completed', $turn['status']);
        $this->assertStringContainsString('Choose File', $turn['speech']);
        $this->assertStringContainsString('Profile Image', $turn['speech']);
    }

    /**
     * Test 7: Spoken correction handling ("nahi 2000 nahi 2500")
     */
    public function test_spoken_correction_handling(): void
    {
        $init = $this->formAi->initGuidedSession('member_wallet_withdrawal', 'member');
        $guideState = $init['guide_state'];

        // User corrects amount during speech
        $turn = $this->formAi->processGuidedTurn('nahi 2000 nahi 2500', 'member_wallet_withdrawal', 'member', [], $guideState);

        $this->assertTrue($turn['success']);
        $this->assertCount(1, $turn['form_updates']);
        $this->assertEquals(2500, $turn['form_updates'][0]['value']);
        $this->assertStringContainsString('बदल कर 2500 कर दिया है', $turn['speech']);
    }

    /**
     * Test 8: Unclear answer keeps active field intact without losing state
     */
    public function test_unclear_answer_keeps_active_field(): void
    {
        $init = $this->formAi->initGuidedSession('member_wallet_withdrawal', 'member');
        $guideState = $init['guide_state'];
        $this->assertEquals('amount', $guideState['current_field']);

        // User says something unrelated / unclear
        $turn = $this->formAi->processGuidedTurn('aaj mausam kaisa hai', 'member_wallet_withdrawal', 'member', [], $guideState);

        $this->assertFalse($turn['success']);
        $this->assertEquals('clarify_field', $turn['status']);
        $this->assertEquals('prompt', $turn['action']);
        $this->assertEmpty($turn['form_updates']);

        // State remains on amount!
        $this->assertEquals('amount', $turn['guide_state']['current_field']);
        $this->assertEquals('amount', $turn['active_field']['key']);
        $this->assertStringContainsString('मैं Withdrawal Amount ($) की value समझ नहीं पाई', $turn['speech']);
        $this->assertStringContainsString('दोबारा बताइए', $turn['speech']);
    }

    /**
     * Test 9: Zero auto-submission verification
     */
    public function test_zero_auto_submission_guarantee(): void
    {
        $init = $this->formAi->initGuidedSession('member_wallet_withdrawal', 'member');
        $turn = $this->formAi->processGuidedTurn('100', 'member_wallet_withdrawal', 'member', [], $init['guide_state']);

        $this->assertEquals('completed', $turn['status']);
        $this->assertEquals('complete', $turn['action']);

        // Assistant must remind user to click submit button manually
        $this->assertStringContainsString('Submit', $turn['speech']);
        $this->assertStringContainsString('manually', $turn['speech']);
        $this->assertArrayNotHasKey('auto_submit', $turn);
        $this->assertArrayNotHasKey('submitted', $turn);
    }

    /**
     * Test 10: Full HTTP Endpoint integration over POST /ira/form-step
     */
    public function test_http_endpoint_guided_session(): void
    {
        // 1. HTTP Init
        $initResponse = $this->withSession(['MEMBER_LOGIN' => true, 'MEMBER_ID' => 1])
            ->postJson('/ira/form-step', [
                'action' => 'init',
                'form_key' => 'member_wallet_withdrawal',
                'current_page' => '/member/wallet',
                'current_fields' => [],
            ]);

        $initResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'asking_field')
            ->assertJsonPath('action', 'prompt')
            ->assertJsonPath('guide_state.current_field', 'amount');

        $guideState = $initResponse->json('guide_state');

        // 2. HTTP Spoken Turn
        $turnResponse = $this->withSession(['MEMBER_LOGIN' => true, 'MEMBER_ID' => 1])
            ->postJson('/ira/form-step', [
                'action' => 'turn',
                'speech' => 'ek sau dollar',
                'form_key' => 'member_wallet_withdrawal',
                'current_page' => '/member/wallet',
                'current_fields' => [],
                'guide_state' => $guideState,
            ]);

        $turnResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('action', 'complete')
            ->assertJsonPath('form_updates.0.value', 100);

        // 3. HTTP Cancel Command
        $cancelResponse = $this->withSession(['MEMBER_LOGIN' => true, 'MEMBER_ID' => 1])
            ->postJson('/ira/form-step', [
                'action' => 'turn',
                'speech' => 'cancel karo',
                'form_key' => 'member_wallet_withdrawal',
                'current_page' => '/member/wallet',
                'guide_state' => $guideState,
            ]);

        $cancelResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'cancelled')
            ->assertJsonPath('action', 'exit');
    }

    /**
     * Test 11: Phase 8 Fix — Devanagari Name transliteration to Title Cased Roman alphabet
     */
    public function test_devanagari_name_transliteration_to_roman_title_case(): void
    {
        $init = $this->formAi->initGuidedSession('member_profile_user_summary', 'member');
        $guideState = $init['guide_state'];
        $this->assertEquals('name', $guideState['current_field']);

        // User speaks Hindi Devanagari name: "अभि कुमार"
        $turn = $this->formAi->processGuidedTurn('अभि कुमार', 'member_profile_user_summary', 'member', [], $guideState);

        $this->assertTrue($turn['success']);
        $this->assertEquals('field_updated', $turn['status']);
        $this->assertCount(1, $turn['form_updates']);
        $this->assertEquals('name', $turn['form_updates'][0]['field']);
        // Must be transliterated to Roman English characters in Title Case
        $this->assertEquals('Abhi Kumar', $turn['form_updates'][0]['value']);
        $this->assertStringContainsString('Abhi Kumar', $turn['speech']);
    }

    /**
     * Test 12: Phase 8 Fix — Devanagari Country transliteration
     */
    public function test_devanagari_country_transliteration(): void
    {
        $guideState = [
            'status' => 'in_progress',
            'current_field' => 'country',
            'field_sequence' => ['name', 'mobile', 'country'],
            'filled_fields' => ['name', 'mobile'],
            'step_index' => 2,
        ];
        $currentFields = ['name' => 'Abhi Kumar', 'mobile' => '9876543210'];

        // User speaks Devanagari "कनाडा"
        $turn = $this->formAi->processGuidedTurn('कनाडा', 'member_profile_user_summary', 'member', $currentFields, $guideState);

        $this->assertTrue($turn['success']);
        $this->assertEquals('completed', $turn['status']);
        $this->assertEquals('Canada', $turn['form_updates'][0]['value']);
    }

    /**
     * Test 13: Phase 8 Fix — Spoken Hindi Free Text transliteration in support tickets
     */
    public function test_devanagari_free_text_transliteration(): void
    {
        $guideState = [
            'status' => 'in_progress',
            'current_field' => 'issue',
            'field_sequence' => ['subject', 'issue'],
            'filled_fields' => ['subject'],
            'step_index' => 1,
        ];
        $currentFields = ['subject' => 'Account Issue'];

        // User speaks "मुझे account access नहीं हो रहा"
        $turn = $this->formAi->processGuidedTurn('मुझे account access नहीं हो रहा', 'member_support_create', 'member', $currentFields, $guideState);

        $this->assertTrue($turn['success']);
        $this->assertEquals('completed', $turn['status']);
        $this->assertEquals('Mujhe account access nahi ho raha', $turn['form_updates'][0]['value']);
    }

    /**
     * Test 14: Phase 8 Fix — Spoken Hindi numeric amounts normalization
     */
    public function test_spoken_hindi_amounts_normalization(): void
    {
        $init = $this->formAi->initGuidedSession('member_wallet_withdrawal', 'member');
        $guideState = $init['guide_state'];

        // 1. "एक हजार पांच सौ" -> 1500
        $turn1 = $this->formAi->processGuidedTurn('एक हजार पांच सौ', 'member_wallet_withdrawal', 'member', [], $guideState);
        $this->assertTrue($turn1['success']);
        $this->assertEquals(1500, $turn1['form_updates'][0]['value']);

        // 2. "बारह हजार" -> 12000
        $turn2 = $this->formAi->processGuidedTurn('बारह हजार', 'member_wallet_withdrawal', 'member', [], $guideState);
        $this->assertTrue($turn2['success']);
        $this->assertEquals(12000, $turn2['form_updates'][0]['value']);
    }

    /**
     * Test 15: Phase 8 Fix — Devanagari numerals phone number parsing
     */
    public function test_devanagari_digits_phone_normalization(): void
    {
        $guideState = [
            'status' => 'in_progress',
            'current_field' => 'mobile',
            'field_sequence' => ['name', 'mobile', 'country'],
            'filled_fields' => ['name'],
            'step_index' => 1,
        ];

        // User speaks Devanagari digits: "९८७६५४३२१०"
        $turn = $this->formAi->processGuidedTurn('९८७६५४३२१०', 'member_profile_user_summary', 'member', ['name' => 'Abhi Kumar'], $guideState);

        $this->assertTrue($turn['success']);
        $this->assertEquals('9876543210', $turn['form_updates'][0]['value']);
    }

    /**
     * Test 16: Phase 8 Fix — Natural Active Field Correction ("नहीं, Abhi नहीं, Abhi Kumar लिखो")
     */
    public function test_natural_correction_pattern_for_name(): void
    {
        $init = $this->formAi->initGuidedSession('member_profile_user_summary', 'member');
        $guideState = $init['guide_state'];

        $turn = $this->formAi->processGuidedTurn('नहीं, Abhi नहीं, Abhi Kumar लिखो', 'member_profile_user_summary', 'member', [], $guideState);

        $this->assertTrue($turn['success']);
        $this->assertEquals('Abhi Kumar', $turn['form_updates'][0]['value']);
        $this->assertStringContainsString('बदल कर Abhi Kumar कर दिया है', $turn['speech']);
    }

    /**
     * Test 17: Phase 8 Fix — Spoken Rewind Command ("पिछली value बदल दो")
     */
    public function test_spoken_rewind_command(): void
    {
        $guideState = [
            'status' => 'in_progress',
            'current_field' => 'mobile',
            'field_sequence' => ['name', 'mobile', 'country'],
            'filled_fields' => ['name'],
            'step_index' => 1,
        ];
        $currentFields = ['name' => 'Abhi Kumar'];

        $turn = $this->formAi->processGuidedTurn('पिछली value बदल दो', 'member_profile_user_summary', 'member', $currentFields, $guideState);

        $this->assertTrue($turn['success']);
        $this->assertEquals('rewind_field', $turn['status']);
        $this->assertEquals('prompt', $turn['action']);
        $this->assertEquals('name', $turn['guide_state']['current_field']);
        $this->assertEmpty($turn['guide_state']['filled_fields']);
        $this->assertStringContainsString('Full Name को दोबारा बदलते हैं', $turn['speech']);
    }

    /**
     * Test 18: Phase 8 Fix — Spoken Field Switch Command ("Mobile Number दोबारा बदलो")
     */
    public function test_spoken_field_switch_command(): void
    {
        $guideState = [
            'status' => 'in_progress',
            'current_field' => 'country',
            'field_sequence' => ['name', 'mobile', 'country'],
            'filled_fields' => ['name', 'mobile'],
            'step_index' => 2,
        ];
        $currentFields = ['name' => 'Abhi Kumar', 'mobile' => '9876543210'];

        $turn = $this->formAi->processGuidedTurn('Mobile Number दोबारा बदलो', 'member_profile_user_summary', 'member', $currentFields, $guideState);

        $this->assertTrue($turn['success']);
        $this->assertEquals('switch_field', $turn['status']);
        $this->assertEquals('prompt', $turn['action']);
        $this->assertEquals('mobile', $turn['guide_state']['current_field']);
        $this->assertStringContainsString('Mobile Number को दोबारा बदलते हैं', $turn['speech']);
    }

    /**
     * Test 19: Alphanumeric Member ID Recognition (English letters & digits, Hindi letter-names, etc.)
     */
    public function test_alphanumeric_member_id_recognition_variations(): void
    {
        $schema = IraFormRegistry::getForm('member_p2p_transfer');
        $init = $this->formAi->initGuidedSession('member_p2p_transfer', 'member');
        $guideState = $init['guide_state'];
        $this->assertEquals('memberid', $guideState['current_field']);

        // Case 1: "Q R V one two three four five six" -> QRV123456
        $turn1 = $this->formAi->processGuidedTurn('Q R V one two three four five six', 'member_p2p_transfer', 'member', [], $guideState);
        $this->assertTrue($turn1['success']);
        $this->assertEquals('QRV123456', $turn1['form_updates'][0]['value']);

        // Case 2: "Q R V 1 2 3 4 5 6" -> QRV123456
        $turn2 = $this->formAi->processGuidedTurn('Q R V 1 2 3 4 5 6', 'member_p2p_transfer', 'member', [], $guideState);
        $this->assertTrue($turn2['success']);
        $this->assertEquals('QRV123456', $turn2['form_updates'][0]['value']);

        // Case 3: "क्यू आर वी वन टू थ्री फोर फाइव सिक्स" -> QRV123456
        $turn3 = $this->formAi->processGuidedTurn('क्यू आर वी वन टू थ्री फोर फाइव सिक्स', 'member_p2p_transfer', 'member', [], $guideState);
        $this->assertTrue($turn3['success']);
        $this->assertEquals('QRV123456', $turn3['form_updates'][0]['value']);

        // Case 4: "क्यू आर वी one two three four five six" -> QRV123456
        $turn4 = $this->formAi->processGuidedTurn('क्यू आर वी one two three four five six', 'member_p2p_transfer', 'member', [], $guideState);
        $this->assertTrue($turn4['success']);
        $this->assertEquals('QRV123456', $turn4['form_updates'][0]['value']);

        // Case 5: "G A B one zero zero two five" -> GAB10025
        $turn5 = $this->formAi->processGuidedTurn('G A B one zero zero two five', 'member_p2p_transfer', 'member', [], $guideState);
        $this->assertTrue($turn5['success']);
        $this->assertEquals('GAB10025', $turn5['form_updates'][0]['value']);

        // Case 6: "GAB10025" -> GAB10025
        $turn6 = $this->formAi->processGuidedTurn('GAB10025', 'member_p2p_transfer', 'member', [], $guideState);
        $this->assertTrue($turn6['success']);
        $this->assertEquals('GAB10025', $turn6['form_updates'][0]['value']);
    }

    /**
     * Test 20: Numeric Identifier Preserves Leading Zeros
     */
    public function test_numeric_identifier_preserves_leading_zeros(): void
    {
        $init = $this->formAi->initGuidedSession('member_p2p_transfer', 'member');
        $guideState = $init['guide_state'];

        // Case 1: "zero five three" -> "053" (Never parsed as integer 53)
        $turn1 = $this->formAi->processGuidedTurn('zero five three', 'member_p2p_transfer', 'member', [], $guideState);
        $this->assertTrue($turn1['success']);
        $this->assertSame('053', $turn1['form_updates'][0]['value']);

        // Case 2: "शून्य पाँच तीन" -> "053"
        $turn2 = $this->formAi->processGuidedTurn('शून्य पाँच तीन', 'member_p2p_transfer', 'member', [], $guideState);
        $this->assertTrue($turn2['success']);
        $this->assertSame('053', $turn2['form_updates'][0]['value']);
    }

    /**
     * Test 21: Digit-by-Digit Speech Formatting in Voice Response
     */
    public function test_digit_by_digit_speech_formatting_voice_response(): void
    {
        $init = $this->formAi->initGuidedSession('member_p2p_transfer', 'member');
        $guideState = $init['guide_state'];

        // Process Member ID in Hindi
        $turn = $this->formAi->processGuidedTurn('Q R V one two three four five six', 'member_p2p_transfer', 'member', [], $guideState, ['language' => 'hi']);
        $this->assertTrue($turn['success']);
        $this->assertEquals('QRV123456', $turn['form_updates'][0]['value']);

        // Spoken confirmation must read digits individually, not as an Indian place-value number
        $this->assertEquals('Q R V एक दो तीन चार पाँच छह', $turn['form_updates'][0]['spoken_value']);
        $this->assertStringContainsString('Q R V एक दो तीन चार पाँच छह', $turn['speech']);

        // Process in English
        $turnEn = $this->formAi->processGuidedTurn('Q R V one two three four five six', 'member_p2p_transfer', 'member', [], $guideState, ['language' => 'en']);
        $this->assertEquals('Q R V one two three four five six', $turnEn['form_updates'][0]['spoken_value']);
        $this->assertStringContainsString('Q R V one two three four five six', $turnEn['speech']);
    }

    /**
     * Test 22: Unclear Identifier Asks Specific Polite Clarification and Retains Active Field
     */
    public function test_unclear_identifier_asks_specific_polite_clarification(): void
    {
        $init = $this->formAi->initGuidedSession('member_p2p_transfer', 'member');
        $guideState = $init['guide_state'];

        // User speaks unclear input for Member ID
        $turn = $this->formAi->processGuidedTurn('kuch bhi ulta seedha bolo', 'member_p2p_transfer', 'member', [], $guideState);

        $this->assertFalse($turn['success']);
        $this->assertEquals('clarify_field', $turn['status']);
        $this->assertEquals('prompt', $turn['action']);
        $this->assertEquals('memberid', $turn['guide_state']['current_field']);
        $this->assertEquals('memberid', $turn['active_field']['key']);
        $this->assertEmpty($turn['form_updates']);

        // Polite clarification message specific to Member ID
        $this->assertStringContainsString('Receiver Member ID ठीक से समझ नहीं पाई', $turn['speech']);
        $this->assertStringContainsString('अक्षर और अंक अलग-अलग बोल सकते हैं', $turn['speech']);
    }

    /**
     * Test 23: Context-Aware Purpose Announcement and Polite Conversation
     */
    public function test_context_aware_purpose_announcement_and_polite_conversation(): void
    {
        $init = $this->formAi->initGuidedSession('member_profile_user_summary', 'member');

        $this->assertTrue($init['success']);
        $this->assertEquals('asking_field', $init['status']);
        $this->assertStringContainsString('बिल्कुल, मैं आपका Edit Profile Details form भरने में मदद करती हूँ', $init['speech']);
        $this->assertStringContainsString('Full Name, Mobile Number और Country जैसे fields हैं', $init['speech']);
        $this->assertStringContainsString('चलिए, पहले Full Name से शुरू करते हैं', $init['speech']);
        $this->assertStringContainsString('आपका पूरा नाम क्या है?', $init['speech']);
    }

    /**
     * Test 24: Cardinal Amount vs Digit Identifier Separation
     */
    public function test_cardinal_amount_vs_digit_identifier_separation(): void
    {
        // 1. Cardinal amount "एक लाख बीस हजार" in amount field becomes 120000
        $initWithdrawal = $this->formAi->initGuidedSession('member_wallet_withdrawal', 'member');
        $turnAmount = $this->formAi->processGuidedTurn('एक लाख बीस हजार', 'member_wallet_withdrawal', 'member', [], $initWithdrawal['guide_state']);
        $this->assertTrue($turnAmount['success']);
        $this->assertEquals(120000, $turnAmount['form_updates'][0]['value']);

        // 2. But in an identifier field, "one two zero" is digit sequence "120", never 120000
        $initP2p = $this->formAi->initGuidedSession('member_p2p_transfer', 'member');
        $turnId = $this->formAi->processGuidedTurn('one two zero', 'member_p2p_transfer', 'member', [], $initP2p['guide_state']);
        $this->assertTrue($turnId['success']);
        $this->assertSame('120', $turnId['form_updates'][0]['value']);
    }

    /**
     * Test 25: Devanagari and Mixed-Language Alphanumeric Identifier Variations
     */
    public function test_devanagari_and_mixed_identifier_variations_resolve_correctly(): void
    {
        $cases = [
            'क्यू आर वी 123456' => 'QRV123456',
            'Q R V one two three four five six' => 'QRV123456',
            'Q R V 1 2 3 4 5 6' => 'QRV123456',
            'क्यू आर वी वन टू थ्री फोर फाइव सिक्स' => 'QRV123456',
            'G A B one zero zero two five' => 'GAB10025',
            'GAB10025' => 'GAB10025',
            'zero five three' => '053',
            'शून्य पाँच तीन' => '053',
            'क्यूआरवी 123456' => 'QRV123456',
            'cue are vee 123456' => 'QRV123456',
            'Receiver Member ID क्यू आर वी 123456' => 'QRV123456',
            'Receiver Member ID QRV123456' => 'QRV123456',
            'जी ए बी एक शून्य शून्य दो पांच' => 'GAB10025',
            'जीएबी 10025' => 'GAB10025',
            'double u 1 2 3' => 'W123',
            'डबल यू 1 2 3' => 'W123',
            'क्यू आर वी १२३४५६' => 'QRV123456',
            'क्यूआरवी123456' => 'QRV123456',
        ];

        foreach ($cases as $spoken => $expected) {
            $norm = \App\Services\AI\IraTextNormalizer::normalizeIdentifier($spoken);
            $this->assertSame($expected, $norm, "Failed normalizing '{$spoken}' to '{$expected}'");
        }

        // Prose without digits/ID clue must be rejected safely
        $this->assertNull(\App\Services\AI\IraTextNormalizer::normalizeIdentifier('kuch bhi ulta seedha bolo'));
    }

    /**
     * Test 26: P2P Wallet Flow - Receiver Member ID populates and advances to Transfer Amount
     */
    public function test_p2p_wallet_receiver_member_id_advances_to_amount(): void
    {
        // 1. Init session
        $init = $this->formAi->initGuidedSession('member_p2p_transfer', 'member', [], 'hi');
        $this->assertTrue($init['success']);
        $this->assertEquals('memberid', $init['active_field']['key']);
        $this->assertEquals('Receiver Member ID', $init['active_field']['label']);

        // 2. User speaks "क्यू आर वी 123456"
        $turn1 = $this->formAi->processGuidedTurn('क्यू आर वी 123456', 'member_p2p_transfer', 'member', [], $init['guide_state'], ['language' => 'hi']);
        $this->assertTrue($turn1['success']);
        $this->assertEquals('field_updated', $turn1['status']);
        $this->assertCount(1, $turn1['form_updates']);
        $this->assertEquals('QRV123456', $turn1['form_updates'][0]['value']);
        $this->assertEquals('memberid', $turn1['form_updates'][0]['field']);
        $this->assertContains('input', $turn1['form_updates'][0]['events']);
        $this->assertContains('change', $turn1['form_updates'][0]['events']);

        // Active field must now be amount
        $this->assertEquals('amount', $turn1['guide_state']['current_field']);
        $this->assertEquals('amount', $turn1['active_field']['key']);
        $this->assertStringContainsString('Receiver Member ID', $turn1['speech']);
        $this->assertStringContainsString('Q R V एक दो तीन चार पाँच छह', $turn1['speech']);

        // 3. User speaks "एक लाख बीस हजार" for Amount
        $turn2 = $this->formAi->processGuidedTurn('एक लाख बीस हजार', 'member_p2p_transfer', 'member', ['memberid' => 'QRV123456'], $turn1['guide_state'], ['language' => 'hi']);
        $this->assertTrue($turn2['success']);
        $this->assertEquals('completed', $turn2['status']);
        $this->assertEquals(120000, $turn2['form_updates'][0]['value']);
        $this->assertStringContainsString('Transaction Password', $turn2['speech']);
        $this->assertStringContainsString('manually', $turn2['speech']);
        $this->assertStringContainsString('Submit', $turn2['speech']);
    }

    /**
     * Test 27: Strict Protection of Navigation AI Source Files
     */
    public function test_navigation_ai_source_files_remain_strictly_untouched(): void
    {
        $assistantJs = base_path('public/ira/ira-assistant.js');
        $navService = base_path('app/Services/AI/IraNavigationService.php');
        $navController = base_path('app/Http/Controllers/IraNavigationController.php');

        $this->assertFileExists($assistantJs);
        $this->assertFileExists($navService);
        $this->assertFileExists($navController);

        // IraAssistant should contain independent navigation logic and not form filler logic
        $jsContent = file_get_contents($assistantJs);
        $this->assertStringContainsString('window.IRA = IraAssistant', $jsContent);
        $this->assertStringNotContainsString('IRA_FORM_FILLER', $jsContent);
    }

    /**
     * Test 28: Admin Add Funds - Only visible editable fields (memberid, wallet_type, amount), txnid omitted
     */
    public function test_admin_funds_add_omits_commented_out_txnid_and_asks_visible_fields_only(): void
    {
        $init = $this->formAi->initGuidedSession('admin_funds_add', 'admin', [], 'hi');

        $this->assertTrue($init['success']);
        $this->assertEquals(['memberid', 'wallet_type', 'amount'], $init['guide_state']['field_sequence']);
        $this->assertNotContains('txnid', $init['guide_state']['field_sequence']);
        $this->assertStringNotContainsString('Transaction ID', $init['speech']);
        $this->assertStringNotContainsString('txnid', $init['speech']);
        $this->assertEquals('memberid', $init['active_field']['key']);

        // Step 1: Member ID
        $turn1 = $this->formAi->processGuidedTurn('QRV123456', 'admin_funds_add', 'admin', [], $init['guide_state']);
        $this->assertTrue($turn1['success']);
        $this->assertEquals('wallet_type', $turn1['active_field']['key']);

        // Step 2: Wallet Type
        $turn2 = $this->formAi->processGuidedTurn('Activation Wallet', 'admin_funds_add', 'admin', ['memberid' => 'QRV123456'], $turn1['guide_state']);
        $this->assertTrue($turn2['success']);
        $this->assertEquals('amount', $turn2['active_field']['key']);

        // Step 3: Amount -> completes without asking for txnid
        $turn3 = $this->formAi->processGuidedTurn('500', 'admin_funds_add', 'admin', ['memberid' => 'QRV123456', 'wallet_type' => 'activation'], $turn2['guide_state']);
        $this->assertTrue($turn3['success']);
        $this->assertEquals('completed', $turn3['status']);
        $this->assertEquals('complete', $turn3['action']);
    }

    /**
     * Test 29: Member Staking - Readonly memberid and sensitive txn_password are skipped from sequence
     */
    public function test_member_staking_skips_readonly_and_sensitive_fields(): void
    {
        $init = $this->formAi->initGuidedSession('member_staking', 'member', [], 'hi');

        $this->assertTrue($init['success']);
        $this->assertEquals(['stak_package', 'amount'], $init['guide_state']['field_sequence']);
        $this->assertNotContains('memberid', $init['guide_state']['field_sequence']);
        $this->assertNotContains('txn_password', $init['guide_state']['field_sequence']);

        // Introduction must mention only package and amount
        $this->assertStringContainsString('Investment Package', $init['speech']);
        $this->assertStringNotContainsString('Member ID', $init['speech']);
        $this->assertStringNotContainsString('Transaction Password', $init['speech']);

        // Complete form filling
        $turn1 = $this->formAi->processGuidedTurn('Silver', 'member_staking', 'member', [], $init['guide_state']);
        $turn2 = $this->formAi->processGuidedTurn('2000', 'member_staking', 'member', ['stak_package' => 'Silver'], $turn1['guide_state']);

        $this->assertEquals('completed', $turn2['status']);
        // Completion speech instructs manual entry of Transaction Password
        $this->assertStringContainsString('Transaction Password', $turn2['speech']);
        $this->assertStringContainsString('manually', $turn2['speech']);
        $this->assertStringContainsString('Submit', $turn2['speech']);
    }

    /**
     * Test 30: Dynamic DOM-Eligible Fields Filtering (DOM Intersect)
     */
    public function test_dom_eligible_fields_filtering(): void
    {
        // Simulate DOM where only 'amount' is visible/editable
        $context = ['dom_eligible_fields' => ['amount']];
        $init = $this->formAi->initGuidedSession('member_staking', 'member', [], 'hi', $context);

        $this->assertTrue($init['success']);
        $this->assertEquals(['amount'], $init['guide_state']['field_sequence']);
        $this->assertEquals('amount', $init['active_field']['key']);
        $this->assertStringContainsString('Investment Amount', $init['active_field']['label']);
    }

    /**
     * Test 31: All-Sensitive Password Change Form returns polite manual entry instruction without listening
     */
    public function test_all_sensitive_form_returns_gracefully(): void
    {
        // member_security_txn_password has only old_password, new_password, confirm_password
        $init = $this->formAi->initGuidedSession('member_security_txn_password', 'member', [], 'hi');

        $this->assertTrue($init['success']);
        $this->assertEquals('all_sensitive', $init['status']);
        $this->assertEquals('complete', $init['action']);
        $this->assertNull($init['active_field']);
        $this->assertStringContainsString('password', $init['speech']);
        $this->assertStringContainsString('manually', $init['speech']);
    }
}
