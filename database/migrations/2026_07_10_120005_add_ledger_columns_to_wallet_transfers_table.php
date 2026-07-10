<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * wallet_transfers already has an existing, unused `masterid` column (confirmed zero
     * references anywhere in the app) which is reused as the transaction reference/correlation
     * id going forward — no new reference column needed. Only balance_before and status are
     * genuinely new.
     */
    public function up(): void
    {
        Schema::table('wallet_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_transfers', 'balance_before')) {
                $table->decimal('balance_before', 20, 8)->nullable()->after('balance');
            }
            if (!Schema::hasColumn('wallet_transfers', 'status')) {
                $table->string('status')->nullable()->default('Completed')->after('particular');
            }
            $table->index('masterid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transfers', function (Blueprint $table) {
            $table->dropIndex(['masterid']);
            if (Schema::hasColumn('wallet_transfers', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('wallet_transfers', 'balance_before')) {
                $table->dropColumn('balance_before');
            }
        });
    }
};
