<?php

namespace Tests\Feature;

use App\Services\AI\FormGeminiBridge;
use App\Services\AI\IraFormRegistry;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IraFormGeminiBridgeTest extends TestCase
{
    protected FormGeminiBridge $bridge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bridge = app(FormGeminiBridge::class);
    }

    /**
     * Test 1: Key Resolution - Explicit form-specific key takes precedence.
     */
    public function test_explicit_form_specific_key_precedence(): void
    {
        Config::set('form_ai.api_key', 'form-specific-test-key-123');

        $this->assertTrue($this->bridge->isConfigured());
        $this->assertEquals('form-specific-test-key-123', $this->bridge->resolveApiKey());
    }

    /**
     * Test 2: Key Resolution - Falls back to CHAT_AI_GEMINI_API_KEY when FORM_AI_GEMINI_API_KEY is null or empty.
     */
    public function test_fallback_to_chat_ai_key_when_form_key_empty(): void
    {
        Config::set('form_ai.api_key', 'chat-ai-fallback-key-456');

        $this->assertTrue($this->bridge->isConfigured());
        $this->assertEquals('chat-ai-fallback-key-456', $this->bridge->resolveApiKey());
    }

    /**
     * Test 3: Key Resolution - Unconfigured state when both keys are empty.
     */
    public function test_unconfigured_state_when_both_keys_absent(): void
    {
        Config::set('form_ai.api_key', null);

        $this->assertFalse($this->bridge->isConfigured());
        $this->assertNull($this->bridge->resolveApiKey());

        $schema = IraFormRegistry::getForm('member_p2p_transfer');
        $result = $this->bridge->extractFieldValues('500 transfer karo', $schema);

        $this->assertFalse($result['success']);
        $this->assertEquals('unconfigured', $result['status']);
    }

    /**
     * Test 4: Endpoint and Header Construction - Verifies exact model, v1beta endpoint and x-goog-api-key header.
     */
    public function test_exact_endpoint_and_header_construction(): void
    {
        Config::set('form_ai.api_key', 'test-valid-api-key-xyz');
        Config::set('form_ai.default_model', 'gemini-3.5-flash-lite');

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'updates' => ['amount' => 500],
                                        'is_correction' => false,
                                        'is_ambiguous' => false,
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $schema = IraFormRegistry::getForm('member_p2p_transfer');
        $result = $this->bridge->extractFieldValues('500 transfer karo', $schema, [], 'amount');

        $this->assertTrue($result['success']);
        $this->assertEquals(500, $result['updates']['amount']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent'
                && $request->hasHeader('x-goog-api-key', 'test-valid-api-key-xyz')
                && $request->method() === 'POST'
                && !str_contains($request->url(), 'test-valid-api-key-xyz'); // Never in query param!
        });
    }

    /**
     * Test 5: Valid Structured JSON Response Parsing (multi-field and correction).
     */
    public function test_valid_structured_json_response_parsing(): void
    {
        Config::set('form_ai.api_key', 'test-api-key');

        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'updates' => [
                                            'memberid' => 'GAB10045',
                                            'amount' => 750,
                                        ],
                                        'is_correction' => true,
                                        'corrected_field' => 'amount',
                                        'is_ambiguous' => false,
                                        'clarification_needed' => false,
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $schema = IraFormRegistry::getForm('member_p2p_transfer');
        $result = $this->bridge->extractFieldValues('GAB10045 ko 500 nahi 750 transfer kar do', $schema);

        $this->assertTrue($result['success']);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('GAB10045', $result['updates']['memberid']);
        $this->assertEquals(750, $result['updates']['amount']);
        $this->assertTrue($result['is_correction']);
        $this->assertEquals('amount', $result['corrected_field']);
    }

    /**
     * Test 6: Markdown-fenced JSON Response Stripping.
     */
    public function test_markdown_fenced_json_response_stripping(): void
    {
        Config::set('form_ai.api_key', 'test-api-key');

        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "```json\n{\"updates\":{\"amount\":1200},\"is_correction\":false}\n```"],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $schema = IraFormRegistry::getForm('member_p2p_transfer');
        $result = $this->bridge->extractFieldValues('1200 karo', $schema);

        $this->assertTrue($result['success']);
        $this->assertEquals(1200, $result['updates']['amount']);
    }

    /**
     * Test 7: Non-JSON Malformed Response Handling.
     */
    public function test_non_json_malformed_response_handling(): void
    {
        Config::set('form_ai.api_key', 'test-api-key');

        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "I am sorry, I could not understand the input."],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $schema = IraFormRegistry::getForm('member_p2p_transfer');
        $result = $this->bridge->extractFieldValues('random text', $schema);

        $this->assertFalse($result['success']);
        $this->assertEquals('malformed_json', $result['status']);
    }

    /**
     * Test 8: HTTP Error 400 Handling.
     */
    public function test_http_400_bad_request_handling(): void
    {
        Config::set('form_ai.api_key', 'test-api-key');
        $schema = IraFormRegistry::getForm('member_p2p_transfer');

        Http::fake(['*' => Http::response(['error' => ['code' => 400]], 400)]);
        $result = $this->bridge->extractFieldValues('500 bhejo', $schema);

        $this->assertFalse($result['success']);
        $this->assertEquals('http_error_400', $result['status']);
    }

    /**
     * Test 9: HTTP Error 401 Unauthorized Handling.
     */
    public function test_http_401_unauthorized_handling(): void
    {
        Config::set('form_ai.api_key', 'test-api-key');
        $schema = IraFormRegistry::getForm('member_p2p_transfer');

        Http::fake(['*' => Http::response(['error' => ['code' => 401]], 401)]);
        $result = $this->bridge->extractFieldValues('500 bhejo', $schema);

        $this->assertFalse($result['success']);
        $this->assertEquals('http_error_401', $result['status']);
    }

    /**
     * Test 10: HTTP Error 429 Rate Limit Handling.
     */
    public function test_http_429_rate_limit_handling(): void
    {
        Config::set('form_ai.api_key', 'test-api-key');
        $schema = IraFormRegistry::getForm('member_p2p_transfer');

        Http::fake(['*' => Http::response(['error' => ['code' => 429]], 429)]);
        $result = $this->bridge->extractFieldValues('500 bhejo', $schema);

        $this->assertFalse($result['success']);
        $this->assertEquals('http_error_429', $result['status']);
    }

    /**
     * Test 11: HTTP Error 500 Server Error Handling.
     */
    public function test_http_500_server_error_handling(): void
    {
        Config::set('form_ai.api_key', 'test-api-key');
        $schema = IraFormRegistry::getForm('member_p2p_transfer');

        Http::fake(['*' => Http::response(['error' => ['code' => 500]], 500)]);
        $result = $this->bridge->extractFieldValues('500 bhejo', $schema);

        $this->assertFalse($result['success']);
        $this->assertEquals('http_error_500', $result['status']);
    }

    /**
     * Test 12: Connection Failure and Timeout Fallback.
     */
    public function test_connection_failure_and_timeout_fallback(): void
    {
        Config::set('form_ai.api_key', 'test-api-key');

        Http::fake([
            '*' => function () {
                throw new ConnectionException('Connection timed out after 3000ms');
            },
        ]);

        $schema = IraFormRegistry::getForm('member_p2p_transfer');
        $result = $this->bridge->extractFieldValues('500 bhejo', $schema);

        $this->assertFalse($result['success']);
        $this->assertEquals('timeout', $result['status']);
        $this->assertEmpty($result['updates']);
    }

    /**
     * Test 13: Sensitive Fields and Uploads Excluded from Prompt and Results.
     */
    public function test_sensitive_fields_strictly_excluded(): void
    {
        Config::set('form_ai.api_key', 'test-api-key');

        Http::fake([
            '*' => function ($request) {
                $body = json_decode($request->body(), true);
                $promptText = $body['contents'][0]['parts'][0]['text'] ?? '';

                // Password must NEVER be in the prompt
                $this->assertStringNotContainsString('txn_password', $promptText);

                // Simulate an untrusted/hallucinated model response that tries to output a password
                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    [
                                        'text' => json_encode([
                                            'updates' => [
                                                'amount' => 500,
                                                'txn_password' => 'secret123', // Malicious or hallucinated
                                            ],
                                        ]),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $schema = IraFormRegistry::getForm('member_p2p_transfer');
        $result = $this->bridge->extractFieldValues('500 amount aur password secret123', $schema);

        $this->assertTrue($result['success']);
        $this->assertEquals(500, $result['updates']['amount']);
        // Crucial: Sensitive field must be discarded by bridge!
        $this->assertArrayNotHasKey('txn_password', $result['updates']);
    }

    /**
     * Test 14: Select/Radio Option Value Canonicalization.
     */
    public function test_option_value_canonicalization(): void
    {
        Config::set('form_ai.api_key', 'test-api-key');

        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'updates' => [
                                            'stak_package' => 'diamond', // Lowercase label
                                        ],
                                        'is_correction' => false,
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $schema = IraFormRegistry::getForm('member_staking');
        $result = $this->bridge->extractFieldValues('diamond package select karo', $schema, [], 'stak_package');

        $this->assertTrue($result['success']);
        // Must canonicalize to exact schema option value: 'Diamond'
        $this->assertEquals('Diamond', $result['updates']['stak_package']);
    }
}
