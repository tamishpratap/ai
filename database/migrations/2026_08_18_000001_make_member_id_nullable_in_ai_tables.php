<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make member_id nullable in all AI tables so that guest users
 * (no MEMBER_ID session) can create conversations without violating
 * the foreign key constraint on member_details.id.
 *
 * The foreign key itself is preserved — NULL simply means "guest / no member".
 */
return new class extends Migration
{
    public function up(): void
    {
        // ai_conversations -----------------------------------------------
        Schema::table('ai_conversations', function (Blueprint $table) {
            // Drop the existing non-nullable FK, then re-add as nullable
            $table->dropForeign(['member_id']);
            $table->unsignedBigInteger('member_id')->nullable()->change();
            $table->foreign('member_id')
                ->references('id')->on('member_details')
                ->cascadeOnDelete();
        });

        // ai_messages ----------------------------------------------------
        Schema::table('ai_messages', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->unsignedBigInteger('member_id')->nullable()->change();
            $table->foreign('member_id')
                ->references('id')->on('member_details')
                ->cascadeOnDelete();
        });

        // ai_usage_logs --------------------------------------------------
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->unsignedBigInteger('member_id')->nullable()->change();
            $table->foreign('member_id')
                ->references('id')->on('member_details')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Revert to NOT NULL — only safe if no NULL rows exist
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->unsignedBigInteger('member_id')->nullable(false)->change();
            $table->foreign('member_id')
                ->references('id')->on('member_details')
                ->cascadeOnDelete();
        });

        Schema::table('ai_messages', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->unsignedBigInteger('member_id')->nullable(false)->change();
            $table->foreign('member_id')
                ->references('id')->on('member_details')
                ->cascadeOnDelete();
        });

        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->unsignedBigInteger('member_id')->nullable(false)->change();
            $table->foreign('member_id')
                ->references('id')->on('member_details')
                ->cascadeOnDelete();
        });
    }
};
