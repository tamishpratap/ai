<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * binary_incomes already has a complete matching-bonus schema (left_forwarded, left_new,
     * total_left, right_forwarded, right_new, total_right, matching, total_amount, flushout,
     * amount, forward_left, forward_right, installments, status) but it is empty and has never
     * been written to. The one missing piece is a per-run date, needed to make the daily binary
     * closing idempotent (one closing per member per date).
     */
    public function up(): void
    {
        Schema::table('binary_incomes', function (Blueprint $table) {
            if (!Schema::hasColumn('binary_incomes', 'closing_date')) {
                $table->date('closing_date')->nullable()->after('memberid');
            }
        });

        Schema::table('binary_incomes', function (Blueprint $table) {
            $table->unique(['memberid', 'closing_date'], 'binary_incomes_member_closing_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('binary_incomes', function (Blueprint $table) {
            $table->dropUnique('binary_incomes_member_closing_date_unique');
            if (Schema::hasColumn('binary_incomes', 'closing_date')) {
                $table->dropColumn('closing_date');
            }
        });
    }
};
