<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Learned User Preferences (PDF 9: Safe Self-Improving Personal System)
        if (!Schema::hasTable('ai_learned_preferences')) {
            Schema::create('ai_learned_preferences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->string('category', 64)->index(); // ide, framework, language, tool, style
                $table->string('key')->index();
                $table->json('value');
                $table->float('confidence_score')->default(0.5);
                $table->integer('occurrence_count')->default(1);
                $table->timestamps();
            });
        }

        // 2. Reusable Learned Skills / Custom Workflows (PDF 9)
        if (!Schema::hasTable('ai_reusable_skills')) {
            Schema::create('ai_reusable_skills', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->string('skill_name');
                $table->string('slug')->index();
                $table->json('trigger_phrases');
                $table->string('action_type', 64);
                $table->json('action_payload');
                $table->boolean('is_active')->default(true);
                $table->integer('success_count')->default(0);
                $table->timestamps();
            });
        }

        // 3. Task Evaluations & User Feedback Store (PDF 9)
        if (!Schema::hasTable('ai_task_evaluations')) {
            Schema::create('ai_task_evaluations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->string('task_description');
                $table->string('agent_used', 64)->default('core');
                $table->enum('feedback', ['positive', 'negative', 'neutral'])->default('positive');
                $table->text('user_comment')->nullable();
                $table->integer('execution_time_ms')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // 4. Security Alerts & Secret Scanning Vulnerabilities (PDF 10: Personal Security AI)
        if (!Schema::hasTable('ai_security_vulnerabilities')) {
            Schema::create('ai_security_vulnerabilities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->string('vuln_type', 64)->index(); // api_key_exposure, secret_in_commit, suspicious_process, missing_backup
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->index();
                $table->string('title');
                $table->text('description');
                $table->string('file_path')->nullable();
                $table->text('remediation')->nullable();
                $table->string('status', 32)->default('detected')->index(); // detected, resolved, ignored
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_security_vulnerabilities');
        Schema::dropIfExists('ai_task_evaluations');
        Schema::dropIfExists('ai_reusable_skills');
        Schema::dropIfExists('ai_learned_preferences');
    }
};
