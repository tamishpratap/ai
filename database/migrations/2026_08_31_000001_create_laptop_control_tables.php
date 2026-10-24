<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Workflows table for reusable routines (e.g. Coding Mode, Focus Mode)
        Schema::create('ai_laptop_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('member_details')->nullOnDelete();
            $table->string('name', 160);
            $table->string('slug', 180)->index();
            $table->text('description')->nullable();
            $table->json('trigger_phrases')->nullable(); // e.g. ["coding mode", "development environment ready karo"]
            $table->json('steps');                       // array of action step objects
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 2. Action audit logs for security & compliance
        Schema::create('ai_laptop_action_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('member_details')->nullOnDelete();
            $table->string('action_type', 80)->index(); // app_launch, file_search, system_control, etc.
            $table->string('target', 255)->nullable();
            $table->string('risk_level', 40)->default('level_1')->index(); // level_1, level_2, level_3
            $table->string('status', 40)->default('executed')->index();    // executed, confirmed, rejected, failed, blocked
            $table->json('details')->nullable();
            $table->text('output')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // 3. Pending confirmations for Level 2 and Level 3 actions
        Schema::create('ai_laptop_pending_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('member_details')->cascadeOnDelete();
            $table->string('token', 80)->unique();
            $table->string('action_type', 80);
            $table->string('risk_level', 40)->default('level_2');
            $table->string('title', 200);
            $table->text('description');
            $table->json('payload');
            $table->timestamp('expires_at')->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_laptop_pending_actions');
        Schema::dropIfExists('ai_laptop_action_audits');
        Schema::dropIfExists('ai_laptop_workflows');
    }
};
