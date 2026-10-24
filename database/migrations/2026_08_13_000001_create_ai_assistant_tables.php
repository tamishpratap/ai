<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('member_id')->constrained('member_details')->cascadeOnDelete();
            $table->string('title', 160)->default('New conversation');
            $table->string('provider', 32)->default('gemini');
            $table->string('model', 100);
            $table->string('mode', 20)->default('voice');
            $table->string('status', 20)->default('active');
            $table->text('summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->index(['member_id', 'last_message_at']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('member_details')->cascadeOnDelete();
            $table->string('role', 20);
            $table->longText('content')->nullable();
            $table->string('input_type', 20)->default('text');
            $table->string('output_type', 20)->default('text');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['conversation_id', 'created_at']);
            $table->index(['member_id', 'created_at']);
        });

        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('member_details')->cascadeOnDelete();
            $table->foreignUuid('conversation_id')->nullable()->constrained('ai_conversations')->nullOnDelete();
            $table->foreignUuid('message_id')->nullable()->constrained('ai_messages')->nullOnDelete();
            $table->string('model', 100);
            $table->string('request_type', 32);
            $table->string('input_type', 20);
            $table->string('output_type', 20);
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('status', 20);
            $table->string('error_type', 80)->nullable();
            $table->timestamps();
            $table->index(['member_id', 'created_at']);
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
