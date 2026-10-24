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
        // 1. Registered Devices Table (Android & Other Authorized Devices)
        if (!Schema::hasTable('ai_devices')) {
            Schema::create('ai_devices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id');
                $table->string('device_uuid', 64)->unique();
                $table->string('device_name', 120);
                $table->string('device_type', 30)->default('phone'); // phone, tablet, laptop, wearable
                $table->string('platform', 30)->default('android'); // android, ios, windows, linux
                $table->string('os_version', 50)->nullable();
                $table->string('app_version', 50)->nullable();
                $table->string('model_name', 100)->nullable();
                $table->string('manufacturer', 100)->nullable();
                $table->string('status', 20)->default('online'); // online, offline, revoked, pending
                $table->integer('battery_level')->nullable();
                $table->boolean('is_charging')->default(false);
                $table->string('network_type', 30)->nullable(); // wifi, cellular, ethernet, none
                $table->string('wifi_ssid', 100)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->unsignedBigInteger('storage_free_mb')->nullable();
                $table->unsignedBigInteger('storage_total_mb')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamp('paired_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('member_id')->references('id')->on('member_details')->onDelete('cascade');
                $table->index(['member_id', 'status']);
                $table->index(['device_uuid', 'status']);
            });
        }

        // 2. Short-Lived Device Pairing Sessions
        if (!Schema::hasTable('device_pairing_sessions')) {
            Schema::create('device_pairing_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id');
                $table->string('pairing_code_hash', 128); // Hashed 6-digit pairing code
                $table->string('session_identifier', 64)->unique(); // Public session lookup UUID
                $table->string('qr_token', 64)->unique(); // Secret QR pairing token
                $table->string('status', 20)->default('pending'); // pending, consumed, expired, cancelled
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->timestamps();

                $table->foreign('member_id')->references('id')->on('member_details')->onDelete('cascade');
                $table->index(['session_identifier', 'status']);
                $table->index(['qr_token', 'status']);
            });
        }

        // 3. Secure Device Authentication Tokens & Credentials
        if (!Schema::hasTable('device_tokens')) {
            Schema::create('device_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('device_id');
                $table->string('token_hash', 128)->unique(); // SHA-256 hash of device secret token
                $table->string('token_type', 30)->default('access'); // access, refresh
                $table->string('name', 100)->default('JARVIS Device Token');
                $table->boolean('is_revoked')->default(false);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->foreign('device_id')->references('id')->on('ai_devices')->onDelete('cascade');
                $table->index(['token_hash', 'is_revoked']);
            });
        }

        // 4. Device Permissions Matrix (Actual runtime permissions on Android)
        if (!Schema::hasTable('device_permissions')) {
            Schema::create('device_permissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('device_id');
                $table->string('permission_key', 50); // camera, microphone, location, notifications, storage
                $table->string('status', 20)->default('not_requested'); // granted, denied, not_requested, restricted
                $table->timestamp('granted_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamp('last_checked_at')->nullable();
                $table->timestamps();

                $table->foreign('device_id')->references('id')->on('ai_devices')->onDelete('cascade');
                $table->unique(['device_id', 'permission_key']);
            });
        }

        // 5. Remote Device Commands Table
        if (!Schema::hasTable('device_commands')) {
            Schema::create('device_commands', function (Blueprint $table) {
                $table->id();
                $table->string('command_uuid', 64)->unique();
                $table->unsignedBigInteger('device_id');
                $table->unsignedBigInteger('member_id');
                $table->string('command_type', 60); // battery_status, device_info, send_notification, ring_phone, capture_photo, get_location, etc.
                $table->json('payload')->nullable();
                $table->string('status', 30)->default('pending'); // pending, sent, received, executing, completed, failed, expired, cancelled, denied
                $table->json('result')->nullable();
                $table->string('error_code', 50)->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('expires_at');
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('executed_at')->nullable();
                $table->timestamps();

                $table->foreign('device_id')->references('id')->on('ai_devices')->onDelete('cascade');
                $table->foreign('member_id')->references('id')->on('member_details')->onDelete('cascade');
                $table->index(['device_id', 'status']);
                $table->index(['member_id', 'status']);
            });
        }

        // 6. Device Security & Action Audit Logs Table
        if (!Schema::hasTable('device_audit_logs')) {
            Schema::create('device_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('device_id')->nullable();
                $table->unsignedBigInteger('member_id');
                $table->string('event_type', 60); // device_paired, device_revoked, device_renamed, permission_updated, command_dispatched, command_executed, command_failed, auth_failed, heartbeat
                $table->json('metadata')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->foreign('device_id')->references('id')->on('ai_devices')->onDelete('set null');
                $table->foreign('member_id')->references('id')->on('member_details')->onDelete('cascade');
                $table->index(['device_id', 'event_type']);
                $table->index(['member_id', 'event_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_audit_logs');
        Schema::dropIfExists('device_commands');
        Schema::dropIfExists('device_permissions');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('device_pairing_sessions');
        Schema::dropIfExists('ai_devices');
    }
};
