<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * None of these tables have ever had a migration in this repo - they were created directly
     * in MySQL outside Laravel's migration system (confirmed via SHOW CREATE TABLE against the
     * live gain_arbitage database during planning). Every later migration in this project ALTERs
     * one of these tables, which only works if the table already exists.
     *
     * This migration recreates each one exactly as verified live, guarded by Schema::hasTable()
     * so it's a total no-op against the already-populated production database, and only actually
     * runs on a fresh environment (local sqlite testing, CI, a new install). This is what makes
     * `php artisan migrate` (and therefore `php artisan test`) work end-to-end from empty.
     */
    public function up(): void
    {
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('password');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('member_details')) {
            Schema::create('member_details', function (Blueprint $table) {
                $table->id();
                $table->string('memberid')->nullable();
                $table->string('sponsorid')->default('');
                $table->string('placementid', 50)->nullable();
                $table->string('name')->default('');
                $table->string('mobile')->nullable();
                $table->string('email')->default('');
                $table->string('password')->default('');
                $table->string('txn_password')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('gender')->nullable();
                $table->string('father_name')->nullable();
                $table->date('dob')->nullable();
                $table->string('occupation')->nullable();
                $table->string('state')->nullable();
                $table->string('country', 100)->nullable();
                $table->string('bank', 100)->nullable();
                $table->string('branch', 100)->nullable();
                $table->integer('account_no')->nullable();
                $table->string('ifsc', 50)->nullable();
                $table->string('holder', 100)->nullable();
                $table->string('wallet_address')->nullable();
                $table->text('profile_image')->nullable();
                $table->enum('position', ['Left', 'Right'])->nullable();
                $table->integer('left_count')->default(0);
                $table->integer('right_count')->default(0);
                $table->integer('left_amount')->default(0);
                $table->integer('right_amount')->default(0);
                $table->integer('left_balance')->default(0);
                $table->integer('right_balance')->default(0);
                $table->string('left_id', 20)->nullable();
                $table->string('right_id', 20)->nullable();
                $table->integer('self_biz')->default(0);
                $table->integer('team_biz')->default(0);
                $table->integer('downline')->default(0);
                $table->integer('package')->nullable()->default(0);
                $table->string('rank', 100)->default('Basic');
                $table->enum('status', ['Active', 'Deactive', 'Temp', 'Blocked'])->default('Temp');
                $table->decimal('wallet', 10, 2)->default(0);
                $table->decimal('p2p_wallet', 10, 2)->default(0);
                $table->decimal('coin_wallet', 10, 2)->default(0);
                $table->enum('kyc_status', ['Verified', 'Submitted', 'Unverified'])->default('Unverified');
                $table->tinyInteger('reward_status')->default(0);
                $table->tinyInteger('level_status')->default(1);
                $table->dateTime('package_activated_at')->nullable();
                $table->dateTime('activated_at')->nullable();
                $table->string('activated_by', 20)->default('User');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('upline_members')) {
            Schema::create('upline_members', function (Blueprint $table) {
                $table->increments('id');
                $table->string('memberid');
                for ($i = 1; $i <= 10; $i++) {
                    $table->string("upline_{$i}")->nullable();
                }
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('set_rates')) {
            Schema::create('set_rates', function (Blueprint $table) {
                $table->increments('id');
                $table->decimal('roi_rate', 10, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('staking_details')) {
            Schema::create('staking_details', function (Blueprint $table) {
                $table->increments('id');
                $table->string('memberid');
                $table->dateTime('invest_date');
                $table->dateTime('break_date')->nullable();
                $table->decimal('invest_amount', 10, 4)->default(0);
                $table->string('package');
                $table->integer('installments')->default(0);
                $table->integer('referral_installments')->nullable()->default(0);
                $table->decimal('break_amount', 10, 4)->nullable()->default(0);
                $table->decimal('deduction', 12, 4)->default(0);
                $table->decimal('rate', 10, 2)->nullable()->default(0);
                $table->string('type', 50)->nullable();
                $table->string('status')->default('Active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('staking_incomes')) {
            Schema::create('staking_incomes', function (Blueprint $table) {
                $table->increments('id');
                $table->date('date');
                $table->string('memberid', 20);
                $table->decimal('total_investment', 10, 4)->default(0);
                $table->decimal('rate', 10, 2)->default(0);
                $table->decimal('amount', 10, 4)->default(0);
                $table->integer('installment')->default(0);
                $table->string('status', 20)->default('Paid');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('level_incomes')) {
            Schema::create('level_incomes', function (Blueprint $table) {
                $table->id();
                $table->string('memberid');
                $table->string('level_id');
                $table->string('level', 50);
                $table->integer('downline');
                $table->decimal('amount', 12, 4)->default(0);
                $table->integer('package')->default(0);
                $table->string('name');
                $table->enum('status', ['Paid', 'Unpaid', 'Flushed'])->default('Unpaid');
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('binary_incomes')) {
            Schema::create('binary_incomes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('memberid', 30);
                $table->decimal('left_forwarded', 12, 2)->default(0);
                $table->decimal('left_new', 10, 2)->default(0);
                $table->decimal('total_left', 12, 2)->default(0);
                $table->decimal('right_forwarded', 12, 2)->default(0);
                $table->decimal('right_new', 10, 2)->default(0);
                $table->decimal('total_right', 12, 2)->default(0);
                $table->decimal('matching', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->decimal('flushout', 10, 2)->default(0);
                $table->decimal('amount', 10, 2)->default(0);
                $table->decimal('forward_left', 10, 2)->default(0);
                $table->decimal('forward_right', 10, 2)->default(0);
                $table->tinyInteger('installments')->default(0);
                $table->string('status', 20)->default('Paid');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('withdrawal_requests')) {
            Schema::create('withdrawal_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->dateTime('request_date');
                $table->dateTime('payment_date')->nullable();
                $table->string('request_id');
                $table->string('txnid', 100)->nullable();
                $table->string('memberid');
                $table->string('name')->nullable();
                $table->string('wallet_address')->nullable();
                $table->string('type');
                $table->decimal('usdt_amount', 12, 4)->nullable();
                $table->decimal('gross_amount', 10, 2)->default(0);
                $table->decimal('service_charge', 10, 4)->default(0);
                $table->decimal('net_amount', 10, 4)->default(0);
                $table->string('admin_comment')->nullable();
                $table->string('status', 100)->default('Pending');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('fund_requests')) {
            Schema::create('fund_requests', function (Blueprint $table) {
                $table->id();
                $table->string('memberid', 15);
                $table->date('date');
                $table->decimal('amount', 10, 4);
                $table->string('tran_id', 100);
                $table->string('type');
                $table->decimal('inr_amount', 10, 2)->default(0);
                $table->text('image');
                $table->enum('status', ['Requested', 'Accepted', 'Cancelled'])->default('Requested');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('import_funds')) {
            Schema::create('import_funds', function (Blueprint $table) {
                $table->id();
                $table->string('memberid', 20);
                $table->string('txnid', 100)->nullable();
                $table->string('orderid')->nullable();
                $table->decimal('usdg_amount', 10, 4)->default(0);
                $table->decimal('amount', 10, 4)->default(0);
                $table->text('image')->nullable();
                $table->enum('type', ['Add', 'Deduct'])->default('Add');
                $table->string('wallet_type')->nullable();
                $table->string('added_by')->nullable();
                $table->enum('status', ['Approved', 'Pending', 'Cancelled'])->default('Approved');
                $table->string('mode')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('package_details')) {
            Schema::create('package_details', function (Blueprint $table) {
                $table->id();
                $table->dateTime('txn_date');
                $table->string('order_id');
                $table->string('memberid');
                $table->string('activated_by')->default('User');
                $table->string('package', 50)->nullable();
                $table->string('package_type')->nullable();
                $table->integer('package_value');
                $table->string('txnid')->nullable();
                $table->string('payment_mode');
                $table->string('status')->default('Active');
                $table->timestamp('activated_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wallet_transfers')) {
            Schema::create('wallet_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('memberid', 100);
                $table->string('masterid')->nullable();
                $table->string('walletType');
                $table->decimal('debit', 10, 2)->default(0);
                $table->decimal('credit', 10, 2)->default(0);
                $table->decimal('balance', 10, 2);
                $table->text('particular');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Intentionally a no-op: on production none of these tables were created by this migration
     * (it was a guarded no-op there), so there is nothing this migration owns to drop.
     */
    public function down(): void
    {
        //
    }
};
