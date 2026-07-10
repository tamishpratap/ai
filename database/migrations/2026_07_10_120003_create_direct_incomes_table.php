<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * direct_incomes only exists as a bare Eloquent model today (App\Models\DirectIncome) —
     * the table itself was never created in the live database, and the only helper that wrote
     * to it (directIncome() in payment_functions.php) has zero call sites anywhere in the app.
     * This creates the real table so the direct referral reward can be wired up in a later phase.
     */
    public function up(): void
    {
        if (Schema::hasTable('direct_incomes')) {
            return;
        }

        Schema::create('direct_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('memberid');
            $table->string('directid');
            $table->string('package')->nullable();
            $table->decimal('amount', 20, 8)->default(0);
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->string('reference')->unique();
            $table->string('status')->default('Paid');
            $table->timestamps();

            $table->index('memberid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_incomes');
    }
};
