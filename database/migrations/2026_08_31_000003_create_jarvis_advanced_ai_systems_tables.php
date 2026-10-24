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
        // 1. Project Brain: Isolated Knowledge Graph Entries
        if (!Schema::hasTable('ai_project_brain_entries')) {
            Schema::create('ai_project_brain_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->unsignedBigInteger('project_id')->nullable()->index();
                $table->string('entry_type', 64)->index(); // stack, architecture, module, api_endpoint, database_entity, known_bug, decision, todo
                $table->string('title');
                $table->text('content');
                $table->string('file_path')->nullable();
                $table->string('status', 32)->default('active')->index(); // active, resolved, deprecated, pending, completed
                $table->integer('priority')->default(2); // 1 = High, 2 = Medium, 3 = Low
                $table->json('tags')->nullable();
                $table->json('relationships')->nullable(); // linked nodes / graph edges
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('project_id')->references('id')->on('ai_projects')->onDelete('cascade');
            });
        }

        // 2. Proactive Autonomous Intelligence: Goals & Execution Pipelines
        if (!Schema::hasTable('ai_autonomous_goals')) {
            Schema::create('ai_autonomous_goals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->unsignedBigInteger('project_id')->nullable()->index();
                $table->string('title');
                $table->text('user_prompt');
                $table->text('interpreted_goal')->nullable();
                $table->string('status', 32)->default('planning')->index(); // planning, awaiting_approval, executing, verifying, completed, failed, rolled_back
                $table->integer('progress_percent')->default(0);
                $table->string('current_milestone')->nullable();
                $table->json('plan')->nullable(); // step-by-step milestone plan
                $table->json('checkpoints')->nullable(); // checkpoint states for rollback
                $table->json('test_results')->nullable(); // validation test outcomes
                $table->json('rollback_metadata')->nullable(); // git commit / file state backups
                $table->boolean('requires_approval')->default(false);
                $table->string('approval_token', 64)->nullable()->index();
                $table->text('final_summary')->nullable();
                $table->timestamps();

                $table->foreign('project_id')->references('id')->on('ai_projects')->onDelete('set null');
            });
        }

        // 3. Multi-Agent System: Atomic Subtasks Assigned to Specialized Agents
        if (!Schema::hasTable('ai_autonomous_subtasks')) {
            Schema::create('ai_autonomous_subtasks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('goal_id')->index();
                $table->string('assigned_agent', 64)->index(); // core, coding, research, computer, vision, memory, planner, security
                $table->integer('step_number')->default(1);
                $table->string('title');
                $table->text('instruction');
                $table->string('status', 32)->default('pending')->index(); // pending, executing, completed, failed, skipped
                $table->json('payload')->nullable();
                $table->json('output_data')->nullable();
                $table->text('result_summary')->nullable();
                $table->json('validation_checks')->nullable();
                $table->timestamps();

                $table->foreign('goal_id')->references('id')->on('ai_autonomous_goals')->onDelete('cascade');
            });
        }

        // 4. Multi-Agent Communication Bus & Shared Blackboard Messages
        if (!Schema::hasTable('ai_agent_messages')) {
            Schema::create('ai_agent_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('goal_id')->nullable()->index();
                $table->string('from_agent', 64)->index();
                $table->string('to_agent', 64)->index();
                $table->string('message_type', 64)->default('task_contract'); // task_contract, delegation, research_findings, verification_result, security_alert
                $table->text('content');
                $table->json('payload')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_agent_messages');
        Schema::dropIfExists('ai_autonomous_subtasks');
        Schema::dropIfExists('ai_autonomous_goals');
        Schema::dropIfExists('ai_project_brain_entries');
    }
};
