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
        // This project's tables were originally created directly in MySQL, outside Laravel's
        // migration system, so admin_comment may already exist live even though this migration
        // has never actually run here — guard keeps it safe against either starting state.
        if (Schema::hasColumn('withdrawal_requests', 'admin_comment')) {
            return;
        }

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->text('admin_comment')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            if (Schema::hasColumn('withdrawal_requests', 'admin_comment')) {
                $table->dropColumn('admin_comment');
            }
        });
    }
};
