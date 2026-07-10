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
        Schema::table('staking_details', function (Blueprint $table) {
            if (!Schema::hasColumn('staking_details', 'is_upgrade')) {
                $table->boolean('is_upgrade')->default(false)->after('package');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staking_details', function (Blueprint $table) {
            if (Schema::hasColumn('staking_details', 'is_upgrade')) {
                $table->dropColumn('is_upgrade');
            }
        });
    }
};
