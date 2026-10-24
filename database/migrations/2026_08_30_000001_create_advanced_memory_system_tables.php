<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Projects table for project-aware context
        Schema::create('ai_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('member_details')->nullOnDelete();
            $table->string('name', 160);
            $table->string('slug', 180)->index();
            $table->text('description')->nullable();
            $table->text('tech_stack')->nullable();
            $table->text('architecture')->nullable();
            $table->text('folders_structure')->nullable();
            $table->text('api_endpoints')->nullable();
            $table->text('known_bugs')->nullable();
            $table->text('fixed_bugs')->nullable();
            $table->text('current_progress')->nullable();
            $table->text('pending_work')->nullable();
            $table->text('next_steps')->nullable();
            $table->string('status', 30)->default('active'); // active, paused, completed, archived
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['member_id', 'slug']);
        });

        // 2. Structured memory items with confidence and categorization
        Schema::create('ai_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('member_details')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('ai_projects')->nullOnDelete();
            $table->string('category', 50)->default('preference'); 
            // preference, project, skill, decision, instruction, task, conversation, other
            $table->string('type', 50)->default('long_term'); 
            // long_term, short_term, project, fact
            $table->string('title', 255);
            $table->longText('content');
            $table->string('confidence_level', 30)->default('medium'); // confirmed, high, medium, low
            $table->decimal('confidence_score', 3, 2)->default(0.70); // 0.00 to 1.00
            $table->string('source', 60)->default('ai_inferred'); 
            // user_explicit, ai_inferred, system_detected, user_edit, project_doc
            $table->boolean('is_confirmed')->default(false);
            $table->boolean('is_sensitive')->default(false);
            $table->unsignedInteger('access_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'category']);
            $table->index(['member_id', 'confidence_level']);
            $table->index(['member_id', 'project_id']);
            $table->index(['member_id', 'is_confirmed']);
            $table->index(['member_id', 'last_accessed_at']);
        });

        // 3. Vector embeddings for semantic search
        Schema::create('ai_memory_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memory_id')->constrained('ai_memories')->cascadeOnDelete();
            $table->string('model', 100)->default('text-embedding-004');
            $table->unsignedSmallInteger('dimensions')->default(768);
            $table->longText('vector'); // JSON array of float values
            $table->timestamps();

            $table->unique(['memory_id', 'model']);
        });

        // 4. Audit logging for memory actions
        Schema::create('ai_memory_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('member_details')->nullOnDelete();
            $table->unsignedBigInteger('memory_id')->nullable();
            $table->string('action', 50); // created, updated, deleted, retrieved, confirmed
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['member_id', 'action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_memory_audit_logs');
        Schema::dropIfExists('ai_memory_embeddings');
        Schema::dropIfExists('ai_memories');
        Schema::dropIfExists('ai_projects');
    }
};
