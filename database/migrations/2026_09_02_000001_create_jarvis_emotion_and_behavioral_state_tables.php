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
        // 1. Emotion & Behavioral States (Simulated AI State Engine)
        if (!Schema::hasTable('ai_emotion_states')) {
            Schema::create('ai_emotion_states', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->string('conversation_id')->nullable()->index();
                $table->string('primary_state', 64)->default('CALM')->index();
                $table->string('secondary_state', 64)->nullable();
                $table->float('intensity')->default(0.5); // 0.00 to 1.00
                $table->float('confidence')->default(0.85); // 0.00 to 1.00
                $table->string('detected_sentiment', 64)->default('neutral')->index();
                $table->text('reason')->nullable();
                $table->text('context_summary')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['member_id', 'primary_state']);
                $table->index(['member_id', 'created_at']);
            });
        }

        // 2. Interaction Events & Emotional Memory History
        if (!Schema::hasTable('ai_interaction_events')) {
            Schema::create('ai_interaction_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->unsignedBigInteger('project_id')->nullable()->index();
                $table->string('event_type', 64)->index(); // task_success, bug_fixed, user_frustration, technical_question, mission_completed, critical_error, etc.
                $table->text('event_description');
                $table->string('context', 255)->nullable();
                $table->string('resulting_state', 64)->default('CALM');
                $table->float('importance')->default(0.5); // 0.00 to 1.00
                $table->boolean('resolved')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['member_id', 'event_type']);
                $table->index(['member_id', 'importance']);
                $table->index(['member_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_interaction_events');
        Schema::dropIfExists('ai_emotion_states');
    }
};
