<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminAiChatUiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->setupTestDatabase();
    }

    protected function setupTestDatabase(): void
    {
        Schema::dropIfExists('admins');
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('password')->default('');
            $table->timestamps();
        });

        DB::table('admins')->insert([
            'id' => 1,
            'email' => 'admin@test.com',
            'password' => 'secret',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test 1: Unauthenticated user is redirected away from GET /admin/ai/chat.
     */
    public function test_unauthenticated_user_cannot_access_admin_chat_page(): void
    {
        $response = $this->get('/admin/ai/chat');
        $response->assertStatus(302);
        $response->assertRedirect('/admin');
    }

    /**
     * Test 2: Authenticated admin can view chat page successfully.
     */
    public function test_authenticated_admin_can_view_chat_page(): void
    {
        $response = $this->withSession(['ADMIN_LOGIN' => true, 'ADMIN_ID' => 1])
            ->get('/admin/ai/chat');

        $response->assertStatus(200);
        $response->assertViewIs('admin.ai.chat');
        $response->assertSee('AI Assistant');
        $response->assertSee('Admin Access Only');
        $response->assertSee('adminHistory');
        $response->assertSee('adminInput');
        $response->assertSee('adminSend');
    }

    /**
     * Test 3: Admin sidebar contains AI Assistant navigation link pointing to /admin/ai/chat.
     */
    public function test_admin_sidebar_contains_ai_assistant_link(): void
    {
        $response = $this->withSession(['ADMIN_LOGIN' => true, 'ADMIN_ID' => 1])
            ->get('/admin/ai/chat');

        $response->assertStatus(200);
        $response->assertSee(url('admin/ai/chat'));
        $response->assertSee('AI Assistant');
        $response->assertSee('ik-cpu');
    }

    /**
     * Test 4: Admin chat page includes report preset buttons.
     */
    public function test_admin_chat_page_includes_presets(): void
    {
        $response = $this->withSession(['ADMIN_LOGIN' => true, 'ADMIN_ID' => 1])
            ->get('/admin/ai/chat');

        $response->assertStatus(200);
        $response->assertSee('System Summary');
        $response->assertSee('Members Report');
        $response->assertSee('Withdrawals Report');
        $response->assertSee('sendPreset');
    }
}
