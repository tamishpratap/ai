<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the flat 2x income cap tracker. Binary business volume and carry-forward reuse
     * member_details' existing (currently unused) left_amount/right_amount/left_balance/
     * right_balance columns instead of adding new ones.
     */
    public function up(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            if (!Schema::hasColumn('member_details', 'capped_income_earned')) {
                $table->decimal('capped_income_earned', 20, 8)->default(0)->after('package');
            }
            if (!Schema::hasColumn('member_details', 'income_cap_limit')) {
                $table->decimal('income_cap_limit', 20, 8)->default(0)->after('capped_income_earned');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            if (Schema::hasColumn('member_details', 'income_cap_limit')) {
                $table->dropColumn('income_cap_limit');
            }
            if (Schema::hasColumn('member_details', 'capped_income_earned')) {
                $table->dropColumn('capped_income_earned');
            }
        });
    }
};
