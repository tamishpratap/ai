<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed the admin-configurable settings this plan introduces.
     *
     * trading_reward_percentage is intentionally NOT here — set_rates.roi_rate stays the single
     * source of truth for that rate (it's already admin-editable via AdminController::rateUpdate),
     * so it isn't duplicated into a second config table.
     *
     * Uses updateOrCreate() keyed by `key`, and only fills in the default `value` when the row
     * doesn't already exist, so re-running this seeder never overwrites an admin's later change.
     */
    public function run(): void
    {
        $defaults = [
            'direct_referral_percentage' => '10',
            'binary_matching_percentage' => '10',
            'binary_closing_status' => 'enabled',
            'binary_closing_time' => '23:59',
            'application_timezone' => 'Asia/Kolkata',
            'maximum_income_percentage' => '200',
            'minimum_withdrawal' => '20',
            'withdrawal_fee_percentage' => '10',
            'p2p_transfer_fee_percentage' => '5',
            'upgrade_payment_mode' => 'full_amount',
            'deposit_status' => 'enabled',
            'withdrawal_status' => 'enabled',
            'p2p_transfer_status' => 'enabled',
            'trading_reward_status' => 'enabled',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
