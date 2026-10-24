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
        // 1. Trusted Devices Table
        if (!Schema::hasTable('ai_laptop_devices')) {
            Schema::create('ai_laptop_devices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id');
                $table->string('device_uuid', 64)->unique();
                $table->string('device_name', 120);
                $table->string('platform', 30)->default('windows');
                $table->string('ip_address', 45)->nullable();
                $table->string('auth_token', 80)->unique();
                $table->string('status', 20)->default('approved'); // pending, approved, revoked
                $table->boolean('is_approved')->default(true);
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('member_id')->references('id')->on('member_details')->onDelete('cascade');
                $table->index(['member_id', 'status']);
            });
        }

        // 2. Project Registry Table
        if (!Schema::hasTable('ai_laptop_projects')) {
            Schema::create('ai_laptop_projects', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id');
                $table->string('name', 150);
                $table->string('slug', 150);
                $table->string('project_path', 255);
                $table->string('project_type', 30)->default('laravel'); // laravel, flutter, python, react, node, generic
                $table->string('preferred_ide', 30)->default('code'); // code, androidstudio, etc.
                $table->string('start_command', 255)->nullable();
                $table->string('stop_command', 255)->nullable();
                $table->json('related_services')->nullable(); // ["apache", "mysql", "laravel_server"]
                $table->string('browser_url', 255)->nullable();
                $table->timestamp('last_opened_at')->nullable();
                $table->timestamps();

                $table->foreign('member_id')->references('id')->on('member_details')->onDelete('cascade');
                $table->index(['member_id', 'slug']);
            });
        }

        // 3. Application Registry Table
        if (!Schema::hasTable('ai_laptop_apps')) {
            Schema::create('ai_laptop_apps', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable();
                $table->string('app_key', 50)->index();
                $table->string('name', 120);
                $table->string('executable_path', 255)->nullable();
                $table->string('process_name', 80);
                $table->boolean('is_installed')->default(true);
                $table->boolean('is_allowlisted')->default(true);
                $table->string('category', 40)->default('development'); // development, browser, utility, system
                $table->timestamps();

                $table->index(['app_key', 'is_allowlisted']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_laptop_apps');
        Schema::dropIfExists('ai_laptop_projects');
        Schema::dropIfExists('ai_laptop_devices');
    }
};
