<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Members who already purchased a package before this system existed have
     * income_cap_limit = 0 (the column's default). Left unfixed, IncomeService::applyIncomeCap()
     * would compute remaining_cap = 0 for them and silently block all future trading/direct/
     * binary income. This is a one-time data backfill, not a schema change: cap = 2x their
     * current package value, matching the same formula PackageService applies going forward.
     */
    public function up(): void
    {
        DB::table('member_details')
            ->where('package', '>', 0)
            ->where('income_cap_limit', 0)
            ->update([
                'income_cap_limit' => DB::raw('package * 2'),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * Not reversible in a lossless way (we can't distinguish a backfilled value from one a real
     * upgrade later set) - intentionally a no-op rather than guessing which rows to revert.
     */
    public function down(): void
    {
        //
    }
};
