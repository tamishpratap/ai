<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MemberDetail;
use App\Services\ChatAi\ChatGeminiBridge;
use App\Services\ChatAi\MemberChatReportService;
use App\Services\ChatAi\AdminChatReportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChatAiBackendTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->setupTestDatabase();
    }

    /**
     * Set up minimal in-memory SQLite tables and seed data for self-contained testing.
     */
    protected function setupTestDatabase(): void
    {
        Schema::dropIfExists('admins');
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('password')->default('');
            $table->timestamps();
        });

        Schema::dropIfExists('member_details');
        Schema::create('member_details', function (Blueprint $table) {
            $table->id();
            $table->string('memberid')->unique();
            $table->string('sponsorid')->default('');
            $table->string('name')->default('');
            $table->string('mobile')->nullable();
            $table->string('email')->default('');
            $table->string('status')->default('Active');
            $table->string('kyc_status')->default('Verified');
            $table->decimal('wallet', 10, 2)->default(0);
            $table->decimal('p2p_wallet', 10, 2)->default(0);
            $table->decimal('package', 10, 2)->default(0);
            $table->decimal('self_biz', 10, 2)->default(0);
            $table->decimal('team_biz', 10, 2)->default(0);
            $table->integer('left_count')->default(0);
            $table->integer('right_count')->default(0);
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('direct_incomes');
        Schema::create('direct_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('memberid');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('Paid');
            $table->timestamps();
        });

        Schema::dropIfExists('level_incomes');
        Schema::create('level_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('memberid');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('Paid');
            $table->timestamps();
        });

        Schema::dropIfExists('staking_incomes');
        Schema::create('staking_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('memberid');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('Paid');
            $table->timestamps();
        });

        Schema::dropIfExists('daily_incomes');
        Schema::create('daily_incomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('Paid');
            $table->timestamps();
        });

        Schema::dropIfExists('reward_incomes');
        Schema::create('reward_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('memberid');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('Paid');
            $table->timestamps();
        });

        Schema::dropIfExists('withdrawal_requests');
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('memberid');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('Approved');
            $table->timestamps();
        });

        Schema::dropIfExists('wallet_transfers');
        Schema::create('wallet_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('memberid');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('type')->default('Credit');
            $table->timestamps();
        });

        Schema::dropIfExists('support_tickets');
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id');
            $table->string('memberid');
            $table->string('subject');
            $table->string('status')->default('Open');
            $table->timestamps();
        });

        Schema::dropIfExists('staking_details');
        Schema::create('staking_details', function (Blueprint $table) {
            $table->id();
            $table->string('memberid');
            $table->decimal('package', 10, 2)->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        // Seed Admin
        DB::table('admins')->insert([
            'id' => 1,
            'email' => 'admin@test.com',
            'password' => 'secret',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed Member 1
        DB::table('member_details')->insert([
            'id' => 1,
            'memberid' => 'QRB123456',
            'sponsorid' => '',
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'status' => 'Active',
            'kyc_status' => 'Verified',
            'wallet' => 250.00,
            'p2p_wallet' => 50.00,
            'package' => 100.00,
            'self_biz' => 100.00,
            'team_biz' => 500.00,
            'left_count' => 5,
            'right_count' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed Member 2
        DB::table('member_details')->insert([
            'id' => 2,
            'memberid' => 'QRB839609',
            'sponsorid' => 'QRB123456',
            'name' => 'Jane Smith',
            'email' => 'jane@test.com',
            'status' => 'Active',
            'kyc_status' => 'Verified',
            'wallet' => 500.00,
            'p2p_wallet' => 100.00,
            'package' => 250.00,
            'self_biz' => 250.00,
            'team_biz' => 1000.00,
            'left_count' => 10,
            'right_count' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed Incomes for Member 1
        DB::table('direct_incomes')->insert([
            'memberid' => 'QRB123456',
            'amount' => 150.00,
            'status' => 'Paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('level_incomes')->insert([
            'memberid' => 'QRB123456',
            'amount' => 75.00,
            'status' => 'Paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('withdrawal_requests')->insert([
            'memberid' => 'QRB123456',
            'amount' => 60.00,
            'status' => 'Approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test 1: Unauthenticated member request is blocked.
     */
    public function test_member_unauthenticated_request_is_blocked(): void
    {
        $response = $this->postJson('/member/ai/chat', [
            'message' => 'What is my total income?',
        ]);

        $this->assertTrue(in_array($response->status(), [401, 302]));
    }

    /**
     * Test 2: Unauthenticated admin request is blocked.
     */
    public function test_admin_unauthenticated_request_is_blocked(): void
    {
        $response = $this->postJson('/admin/ai/chat', [
            'message' => 'Show platform overview',
        ]);

        $this->assertTrue(in_array($response->status(), [401, 302]));
    }

    /**
     * Test 3: Member request rejects empty message/prompt.
     */
    public function test_member_validation_rejects_empty_prompt(): void
    {
        $response = $this->withSession(['MEMBER_ID' => 'QRB123456'])
            ->postJson('/member/ai/chat', [
                'message' => '',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test 4: Member IDOR defense prevents cross-member data access.
     */
    public function test_member_idor_defense_blocks_cross_member_lookup(): void
    {
        $response = $this->withSession(['MEMBER_ID' => 'QRB123456'])
            ->postJson('/member/ai/chat', [
                'message' => 'Give me all incomes and balance for member QRB839609',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'security_shield',
            ]);

        $this->assertStringContainsString('doosri Member ID', $response->json('answer'));
    }

    /**
     * Test 5: Member SQL injection keywords trigger safe rejection.
     */
    public function test_member_sql_injection_defense(): void
    {
        $response = $this->withSession(['MEMBER_ID' => 'QRB123456'])
            ->postJson('/member/ai/chat', [
                'message' => 'UNION SELECT * FROM member_details WHERE id=1',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'security_shield',
            ]);

        $this->assertStringContainsString('verified data', $response->json('answer'));
    }

    /**
     * Test 6: Member income intent gathers real figures and serves deterministic fallback.
     */
    public function test_member_income_intent_aggregation_and_fallback(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(null, 500),
        ]);

        $response = $this->withSession(['MEMBER_ID' => 'QRB123456'])
            ->postJson('/member/ai/chat', [
                'message' => 'What is my total income and earnings breakdown?',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'fallback',
            ]);

        $reply = $response->json('answer');
        $this->assertNotEmpty($reply);
        $this->assertStringContainsString('Income', $reply);
        $this->assertEquals('income', $response->json('context.intent'));
    }

    /**
     * Test 7: Member deposit and withdrawal intent reports transactions.
     */
    public function test_member_deposit_and_withdrawal_intent(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(null, 500),
        ]);

        $response = $this->withSession(['MEMBER_ID' => 'QRB123456'])
            ->postJson('/member/ai/chat', [
                'message' => 'Show my withdrawal requests and deposit history',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'fallback',
            ]);

        $reply = $response->json('answer');
        $this->assertNotEmpty($reply);
        $this->assertTrue(
            stripos($reply, 'withdrawal') !== false || stripos($reply, 'deposit') !== false || stripos($reply, 'transaction') !== false
        );
    }

    /**
     * Test 8: Admin global metrics report.
     */
    public function test_admin_global_metrics_report(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(null, 500),
        ]);

        $response = $this->withSession(['ADMIN_LOGIN' => true, 'ADMIN_ID' => 1])
            ->postJson('/admin/ai/chat', [
                'message' => 'Show overall platform statistics and total members',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'fallback',
            ]);

        $reply = $response->json('answer');
        $this->assertNotEmpty($reply);
        $this->assertTrue(str_contains($reply, 'Membership') || str_contains($reply, 'Total Registered'));
        $this->assertTrue(in_array($response->json('context.intent'), ['global_metrics', 'global_members']));
    }

    /**
     * Test 9: Admin candidate member lookup succeeds for valid member.
     */
    public function test_admin_candidate_member_lookup(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(null, 500),
        ]);

        $response = $this->withSession(['ADMIN_LOGIN' => true, 'ADMIN_ID' => 1])
            ->postJson('/admin/ai/chat', [
                'message' => 'Show complete summary for member QRB839609',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'fallback',
            ]);

        $reply = $response->json('answer');
        $this->assertNotEmpty($reply);
        $this->assertStringContainsString('QRB839609', $reply);
        $this->assertEquals('QRB839609', $response->json('context.target_member'));
    }

    /**
     * Test 10: Admin lookup returns error message when member does not exist.
     */
    public function test_admin_nonexistent_member_lookup(): void
    {
        $response = $this->withSession(['ADMIN_LOGIN' => true, 'ADMIN_ID' => 1])
            ->postJson('/admin/ai/chat', [
                'message' => 'Show report for member QRBNONEXISTENT999',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'fallback',
            ]);

        $this->assertStringContainsString('No member was found', $response->json('answer'));
    }

    /**
     * Test 11: Gemini Bridge integration returns synthesized response when API succeeds.
     */
    public function test_gemini_bridge_successful_response(): void
    {
        // Provide mock api key for this test
        Config::set('chat_ai.api_key', 'AIzaSyMockTestKey12345');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Hello! Based on your records, your total income across all streams is $225.00.']
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->withSession(['MEMBER_ID' => 'QRB123456'])
            ->postJson('/member/ai/chat', [
                'message' => 'What is my total income?',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'gemini',
            ]);

        $this->assertStringContainsString('$225.00', $response->json('answer'));
    }

    /**
     * Test 12: Gemini Bridge timeout or failure seamlessly serves deterministic fallback.
     */
    public function test_gemini_bridge_timeout_or_failure_fallback(): void
    {
        Config::set('chat_ai.api_key', 'AIzaSyMockTestKey12345');

        Http::fake([
            'generativelanguage.googleapis.com/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timed out after 5000ms');
            },
        ]);

        $response = $this->withSession(['MEMBER_ID' => 'QRB123456'])
            ->postJson('/member/ai/chat', [
                'message' => 'What is my total income?',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'fallback',
            ]);

        $this->assertNotEmpty($response->json('answer'));
        $this->assertStringContainsString('Income', $response->json('answer'));
    }

    /**
     * Test 13: Strict isolation protection.
     * Verify that ChatAi configuration is separate from config('gemini.api_key').
     */
    public function test_strict_isolation_protection(): void
    {
        $chatModel = config('chat_ai.model');
        $this->assertEquals('gemini-3.5-flash-lite', $chatModel);

        $envFile = file_get_contents(base_path('.env'));
        $this->assertStringContainsString('CHAT_AI_GEMINI_API_KEY=', $envFile);
        $this->assertStringContainsString('CHAT_AI_GEMINI_MODEL=gemini-3.5-flash-lite', $envFile);

        // Ensure Navigation AI config was NOT populated with the Chat AI key
        $legacyKey = config('gemini.api_key');
        $this->assertEmpty($legacyKey, 'Legacy navigation GEMINI_API_KEY must remain untouched.');
    }
}
