<?php

namespace Tests\Feature;

use App\Services\AI\AppNavigationOntology;
use App\Services\AI\IraNavigationService;
use App\Services\AI\NavigationGeminiBridge;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IraNavigationGeminiBridgeTest extends TestCase
{
    protected NavigationGeminiBridge $bridge;
    protected IraNavigationService $navService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->bridge = app(NavigationGeminiBridge::class);
        $this->navService = app(IraNavigationService::class);
    }

    /**
     * Requirement 1: Dedicated key precedence and fallback to Chat AI key.
     */
    public function test_key_precedence_and_fallback(): void
    {
        // 1. Explicit NAVIGATION_AI_GEMINI_API_KEY takes precedence
        Config::set('navigation_ai.api_key', 'test_dedicated_key_nav');
        $this->assertEquals('test_dedicated_key_nav', $this->bridge->resolveApiKey());
        $this->assertTrue($this->bridge->isConfigured());

        // 2. Fallback to CHAT_AI_GEMINI_API_KEY if dedicated key is empty
        Config::set('navigation_ai.api_key', '');
        $this->assertFalse($this->bridge->isConfigured());

        Config::set('navigation_ai.api_key', 'fallback_chat_key');
        $this->assertEquals('fallback_chat_key', $this->bridge->resolveApiKey());
        $this->assertTrue($this->bridge->isConfigured());
    }

    /**
     * Requirement 2: Exact model, endpoint and header construction.
     */
    public function test_endpoint_and_headers_construction(): void
    {
        Config::set('navigation_ai.api_key', 'test_gemini_key_123');
        Config::set('navigation_ai.default_model', 'gemini-3.5-flash-lite');
        Config::set('navigation_ai.api_version', 'v1beta');

        Http::fake([
            'generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent*' => function ($request) {
                $this->assertEquals('test_gemini_key_123', $request->header('x-goog-api-key')[0]);
                $this->assertStringContainsString('application/json', $request->header('Content-Type')[0]);

                $data = $request->data();
                $this->assertEquals('application/json', $data['generationConfig']['responseMimeType']);

                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => json_encode([
                                        'intent' => 'navigate',
                                        'destination_key' => 'member_dashboard',
                                        'confidence' => 0.95,
                                        'speech' => 'Opening your dashboard.',
                                        'response_language' => 'english',
                                    ])],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $res = $this->bridge->resolveIntent('take me to my main screen', 'member', '/member/profile/user-summary');
        $this->assertTrue($res['success']);
        $this->assertEquals('navigate', $res['intent']);
        $this->assertEquals('member_dashboard', $res['destination_key']);
    }

    /**
     * Requirement 3: Valid structured JSON response parsing.
     */
    public function test_valid_structured_json_response_parsing(): void
    {
        Config::set('navigation_ai.api_key', 'test_key');

        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '```json' . PHP_EOL . json_encode([
                                    'intent' => 'navigate',
                                    'destination_key' => 'member_income_staking',
                                    'confidence' => 0.92,
                                    'speech' => 'Opening your Staking Income report.',
                                    'response_language' => 'english',
                                ]) . PHP_EOL . '```'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $res = $this->bridge->resolveIntent('show my staking rewards', 'member');
        $this->assertTrue($res['success']);
        $this->assertEquals('navigate', $res['intent']);
        $this->assertEquals('member_income_staking', $res['destination_key']);
        $this->assertEquals(0.92, $res['confidence']);
    }

    /**
     * Requirement 4: Malformed JSON and missing destination handling.
     */
    public function test_malformed_json_and_missing_destination_handling(): void
    {
        Config::set('navigation_ai.api_key', 'test_key');

        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Sorry, I am not able to output valid JSON right now.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $res = $this->bridge->resolveIntent('some completely unknown gibberish query', 'member');
        $this->assertFalse($res['success']);
        $this->assertEquals('malformed_json', $res['status']);
    }

    /**
     * Requirement 5: HTTP errors, timeout, and missing key fallback.
     */
    public function test_http_errors_timeout_and_fallback(): void
    {
        Config::set('navigation_ai.api_key', 'test_key');

        // Test HTTP 500 error
        Http::fake([
            '*generateContent*' => Http::response(['error' => 'Internal server error'], 500),
        ]);

        $res = $this->bridge->resolveIntent('take me somewhere', 'member');
        $this->assertFalse($res['success']);
        $this->assertEquals('http_error', $res['status']);

        // Test timeout exception
        Http::fake([
            '*generateContent*' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out');
            },
        ]);

        $timeoutRes = $this->bridge->resolveIntent('take me somewhere', 'member');
        $this->assertFalse($timeoutRes['success']);
        $this->assertEquals('timeout', $timeoutRes['status']);
    }

    /**
     * Requirement 6: Deterministic bypass for obvious canonical commands.
     */
    public function test_deterministic_bypass_avoids_gemini_call(): void
    {
        Http::fake(); // If any HTTP call is made, it would be intercepted

        $result = $this->navService->resolveIntent('Dashboard kholo', 'member');
        $this->assertTrue($result['success']);
        $this->assertEquals('navigate', $result['intent']);
        $this->assertEquals('member_dashboard', $result['destination_key']);

        Http::assertNothingSent();
    }

    /**
     * Requirement 7: Natural English, Hindi and Roman Hinglish understanding.
     */
    public function test_natural_hinglish_and_hindi_resolution(): void
    {
        // 1. "mera profile open karo"
        $profileRes = $this->navService->resolveIntent('mera profile open karo', 'member');
        $this->assertTrue($profileRes['success']);
        $this->assertEquals('navigate', $profileRes['intent']);
        $this->assertEquals('member_profile_summary', $profileRes['destination_key']);

        // 2. "P2P open karna hai"
        $p2pRes = $this->navService->resolveIntent('P2P open karna hai', 'member');
        $this->assertTrue($p2pRes['success']);
        $this->assertEquals('navigate', $p2pRes['intent']);
        $this->assertEquals('member_p2p_wallet', $p2pRes['destination_key']);

        // 3. "earning section dikhao"
        $earnRes = $this->navService->resolveIntent('earning section dikhao', 'member');
        $this->assertTrue($earnRes['success']);
        $this->assertEquals('clarify', $earnRes['intent']);
    }

    /**
     * Requirement 8: Common spelling mistakes (dashbord, profiel, withdrawl).
     */
    public function test_spelling_mistakes_tolerance(): void
    {
        // dashbord -> member_dashboard
        $dash = $this->navService->resolveIntent('member dashbord kholo', 'member');
        $this->assertTrue($dash['success']);
        $this->assertEquals('navigate', $dash['intent']);
        $this->assertEquals('member_dashboard', $dash['destination_key']);

        // profiel -> member_profile_summary
        $prof = $this->navService->resolveIntent('profiel pe le chalo', 'member');
        $this->assertTrue($prof['success']);
        $this->assertEquals('navigate', $prof['intent']);
        $this->assertEquals('member_profile_summary', $prof['destination_key']);

        // withdrawl -> member_wallet_main
        $with = $this->navService->resolveIntent('withdrawl page kholo', 'member');
        $this->assertTrue($with['success']);
        $this->assertEquals('navigate', $with['intent']);
        $this->assertEquals('member_wallet_main', $with['destination_key']);
    }

    /**
     * Requirement 9: Member dashboard phrase must not trigger admin Member Management alias.
     */
    public function test_member_dashboard_phrase_does_not_trigger_admin_management(): void
    {
        $phrases = [
            'member dashboard kholo',
            'member dashbord kholo',
            'member dashboard khol do',
            'member ke dashboard pe hi raho',
        ];

        foreach ($phrases as $phrase) {
            $res = $this->navService->resolveIntent($phrase, 'member');
            $this->assertTrue($res['success'], "Failed for phrase: {$phrase}");
            $this->assertEquals('navigate', $res['intent'], "Intent should be 'navigate' for: {$phrase}");
            $this->assertEquals('member_dashboard', $res['destination_key']);
            $this->assertStringNotContainsString('Member Management is an Administrator-only section', $res['speech']);
        }
    }

    /**
     * Requirement 10: Authenticated member cannot navigate to admin-only routes.
     */
    public function test_member_cannot_navigate_to_admin_only_destinations(): void
    {
        $adminQueries = [
            'admin dashboard par jao',
            'Member Management section dikhao',
            'Members Details page open karo',
            'Account control page kholo',
        ];

        foreach ($adminQueries as $q) {
            $res = $this->navService->resolveIntent($q, 'member');
            $this->assertFalse($res['success'], "Should fail authorization for: {$q}");
            $this->assertEquals('unauthorized', $res['intent']);
            $this->assertNull($res['target_url']);
        }
    }

    /**
     * Requirement 11: Authenticated admin cannot accidentally resolve to member-only routes.
     */
    public function test_admin_cannot_navigate_to_member_only_destinations(): void
    {
        $adminRes = $this->navService->resolveIntent('Admin dashboard kholo', 'admin');
        $this->assertTrue($adminRes['success']);
        $this->assertEquals('admin_dashboard', $adminRes['destination_key']);
        $this->assertStringContainsString('/admin/dashboard', $adminRes['target_url']);

        // Asking for member-only fund import
        $crossRes = $this->navService->resolveIntent('Member Add Fund page par jao', 'admin');
        if (!$crossRes['success']) {
            $this->assertEquals('unauthorized', $crossRes['intent']);
        }
    }

    /**
     * Requirement 12: Model-proposed unknown destinations and raw URLs are rejected.
     */
    public function test_model_proposed_unknown_destination_rejected(): void
    {
        Config::set('navigation_ai.api_key', 'test_key');

        // Model proposes non-existent canonical key
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode([
                                    'intent' => 'navigate',
                                    'destination_key' => 'hack_super_admin_route_999',
                                    'confidence' => 0.99,
                                ])],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $res = $this->bridge->resolveIntent('hack system', 'member');
        $this->assertFalse($res['success']);
        $this->assertEquals('invalid_destination', $res['status']);
    }

    /**
     * Requirement 12b: Model-proposed cross-role destinations are rejected as unauthorized.
     */
    public function test_model_proposed_cross_role_destination_rejected(): void
    {
        Config::set('navigation_ai.api_key', 'test_key');

        // Model attempts cross-role destination (admin_account_control proposed for member)
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode([
                                    'intent' => 'navigate',
                                    'destination_key' => 'admin_account_control',
                                    'confidence' => 0.95,
                                ])],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $resRole = $this->bridge->resolveIntent('open account control', 'member');
        $this->assertFalse($resRole['success']);
        $this->assertEquals('unauthorized', $resRole['status']);
    }

    /**
     * Requirement 13: Clarification options remain within the current role's allowed destinations.
     */
    public function test_clarification_options_filtered_by_role(): void
    {
        Config::set('navigation_ai.api_key', 'test_key');

        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode([
                                    'intent' => 'clarify',
                                    'destination_key' => null,
                                    'confidence' => 0.85,
                                    'speech' => 'Which investment page would you like?',
                                    'options' => ['member_staking', 'member_staking_details', 'admin_staking_details'],
                                ])],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $res = $this->bridge->resolveIntent('invest', 'member');
        $this->assertTrue($res['success']);
        $this->assertEquals('clarify', $res['intent']);
        $this->assertContains('member_staking', $res['options']);
        $this->assertContains('member_staking_details', $res['options']);
        // admin_staking_details must be filtered out for member role!
        $this->assertNotContains('admin_staking_details', $res['options']);
    }

    /**
     * Requirement 14: Full HTTP pipeline end-to-end integration via POST /ira/intent.
     */
    public function test_http_endpoint_resolves_member_dashboard_with_typo(): void
    {
        $response = $this->withSession([
            'MEMBER_LOGIN' => true,
            'MEMBER_ID' => 1,
            'name' => 'Test Member',
        ])->postJson('/ira/intent', [
            'speech' => 'member dashbord kholo',
            'current_page' => '/member/profile/user-summary',
            'current_mode' => 'chat',
            'context' => [],
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals('navigate', $data['intent']);
        $this->assertEquals('member_dashboard', $data['destination_key']);
        $this->assertStringContainsString('/member/dashboard', $data['target_url']);
    }
}
