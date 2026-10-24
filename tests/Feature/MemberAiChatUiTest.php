<?php

namespace Tests\Feature;

use App\Models\MemberDetail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MemberAiChatUiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->setupTestDatabase();
    }

    protected function setupTestDatabase(): void
    {
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

        DB::table('member_details')->insert([
            'id' => 1,
            'memberid' => 'QRB123456',
            'sponsorid' => '',
            'name' => 'Demo Member',
            'email' => 'demo@test.com',
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
    }

    /**
     * Test 1: Unauthenticated user is redirected away from GET /member/ai/chat.
     */
    public function test_unauthenticated_user_cannot_access_member_chat_page(): void
    {
        $response = $this->get('/member/ai/chat');
        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /**
     * Test 2: Authenticated member can open GET /member/ai/chat successfully.
     */
    public function test_authenticated_member_can_view_chat_page(): void
    {
        $response = $this->withSession(['MEMBER_ID' => 'QRB123456'])
            ->get('/member/ai/chat');

        $response->assertStatus(200);
        $response->assertViewIs('member.ai.chat');
        $response->assertSee('AI Assistant');
        $response->assertSee('QRB123456');
        $response->assertSee('chatHistory');
        $response->assertSee('chatInput');
        $response->assertSee('sendBtn');
    }

    /**
     * Test 3: Sidebar contains AI Assistant navigation link pointing to /member/ai/chat.
     */
    public function test_sidebar_contains_ai_assistant_link(): void
    {
        $response = $this->withSession(['MEMBER_ID' => 'QRB123456'])
            ->get('/member/ai/chat');

        $response->assertStatus(200);
        $response->assertSee(url('/member/ai/chat'));
        $response->assertSee('AI Assistant');
        $response->assertSee('side-menu__item');
    }

    /**
     * Test 4: Member chat page includes quick query suggestions.
     */
    public function test_chat_page_includes_quick_queries(): void
    {
        $response = $this->withSession(['MEMBER_ID' => 'QRB123456'])
            ->get('/member/ai/chat');

        $response->assertStatus(200);
        $response->assertSee('Wallet Balance?');
        $response->assertSee('Aaj ki Income?');
        $response->assertSee('sendSuggestion');
    }
}
