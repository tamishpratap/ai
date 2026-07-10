<?php

namespace Tests\Feature\Mlm;

use App\Models\MemberDetail;
use App\Models\SetRate;
use Database\Seeders\PackageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class MlmTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PackageSeeder::class);
        $this->seed(SettingSeeder::class);

        $rate = new SetRate();
        $rate->roi_rate = 0.75;
        $rate->save();
    }

    /**
     * Creates a member directly (matching this app's own convention of direct property
     * assignment rather than mass assignment/factories - none of these models have factories).
     */
    protected function makeMember(array $attributes = []): MemberDetail
    {
        $member = new MemberDetail();
        $member->memberid = $attributes['memberid'] ?? 'GAB' . random_int(100000, 999999);
        $member->sponsorid = $attributes['sponsorid'] ?? 'Root';
        $member->placementid = $attributes['placementid'] ?? 'Root';
        $member->position = $attributes['position'] ?? null;
        $member->name = $attributes['name'] ?? 'Test Member';
        $member->email = $attributes['email'] ?? strtolower($member->memberid) . '@example.test';
        $member->password = bcrypt('password');
        $member->txn_password = bcrypt('123456');
        $member->status = $attributes['status'] ?? 'Temp';
        $member->wallet = $attributes['wallet'] ?? 0;
        $member->p2p_wallet = $attributes['p2p_wallet'] ?? 0;
        $member->package = $attributes['package'] ?? 0;
        $member->income_cap_limit = $attributes['income_cap_limit'] ?? 0;
        $member->capped_income_earned = $attributes['capped_income_earned'] ?? 0;
        $member->left_id = $attributes['left_id'] ?? null;
        $member->right_id = $attributes['right_id'] ?? null;
        $member->wallet_address = $attributes['wallet_address'] ?? '0xTestWalletAddress';
        $member->save();

        return $member;
    }
}
