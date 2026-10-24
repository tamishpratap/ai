<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_image_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('member_details')->cascadeOnDelete();
            $table->text('prompt');
            $table->string('aspect_ratio', 10)->default('1:1');
            $table->string('quality', 20)->default('low');
            $table->string('provider', 40);
            $table->string('model', 120);
            $table->string('storage_disk', 40)->default('local');
            $table->string('storage_path')->nullable();
            $table->string('mime_type', 80)->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('error_message', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['member_id', 'status', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_image_generations');
    }
};
