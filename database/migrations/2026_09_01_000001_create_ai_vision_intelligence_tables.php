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
        // 1. Vision Settings: Configuration, Monitor Scope, Screen Watch Interval & Privacy Rules
        if (!Schema::hasTable('ai_vision_settings')) {
            Schema::create('ai_vision_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->boolean('screen_watch_enabled')->default(false);
                $table->boolean('screen_watch_paused')->default(false);
                $table->integer('watch_interval_sec')->default(15);
                $table->string('default_scope', 32)->default('primary'); // primary, active_window, all_monitors
                $table->string('default_mode', 32)->default('debug');    // debug, quick_scan, ui_review, code_review, study, compare, explain, watch
                $table->json('blocked_apps')->nullable();                // apps/windows excluded for privacy
                $table->json('allowed_apps')->nullable();                // allowlist if enabled
                $table->boolean('save_history')->default(false);          // retain screenshot metadata
                $table->integer('retention_days')->default(7);
                $table->json('privacy_regions')->nullable();             // masked coordinates
                $table->timestamps();
            });
        }

        // 2. Vision Sessions: Historical record of screen inspections & diagnoses
        if (!Schema::hasTable('ai_vision_sessions')) {
            Schema::create('ai_vision_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->string('session_uid', 64)->unique();
                $table->string('vision_mode', 32)->default('debug')->index();
                $table->string('scope', 32)->default('primary');
                $table->string('active_window')->nullable();
                $table->text('user_query')->nullable();
                $table->string('confidence', 32)->default('highly_likely'); // confirmed, highly_likely, possible, unclear
                $table->text('main_issue')->nullable();
                $table->json('observations')->nullable();
                $table->text('root_cause')->nullable();
                $table->text('recommended_fix')->nullable();
                $table->json('alternatives')->nullable();
                $table->json('parsed_error')->nullable();
                $table->json('suggested_actions')->nullable();
                $table->json('ui_review_data')->nullable();
                $table->json('performance_metrics')->nullable();
                $table->string('screenshot_path')->nullable();
                $table->text('ocr_sample')->nullable();
                $table->timestamps();
            });
        }

        // 3. Proactive Screen Watch Events Log
        if (!Schema::hasTable('ai_vision_watch_events')) {
            Schema::create('ai_vision_watch_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->string('event_category', 64)->index(); // error_detected, build_failure, test_failed, session_expired, app_hang
                $table->string('notification_level', 32)->default('Informational')->index(); // Silent, Informational, Important, Critical
                $table->string('active_window')->nullable();
                $table->text('message');
                $table->json('details')->nullable();
                $table->boolean('is_acknowledged')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_vision_watch_events');
        Schema::dropIfExists('ai_vision_sessions');
        Schema::dropIfExists('ai_vision_settings');
    }
};
