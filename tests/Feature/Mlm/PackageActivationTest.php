<?php

namespace Tests\Feature\Mlm;

use App\Models\DirectIncome;
use App\Services\PackageService;

class PackageActivationTest extends MlmTestCase
{
    public function test_activation_debits_wallet_and_activates_temp_member(): void
    {
        $sponsor = $this->makeMember(['status' => 'Active', 'package' => 100, 'income_cap_limit' => 200]);
        $member = $this->makeMember(['sponsorid' => $sponsor->memberid, 'placementid' => $sponsor->memberid, 'position' => 'Left', 'p2p_wallet' => 1000]);

        $result = app(PackageService::class)->activatePackage($member->memberid, 25);

        $member->refresh();
        $this->assertSame('Active', $member->status);
        $this->assertEquals(25, $member->package);
        $this->assertEquals(975, $member->p2p_wallet);
        $this->assertEquals(50, $member->income_cap_limit); // 2x package (confirmed policy)
        $this->assertSame('Starter', $result['package']);
    }

    public function test_duplicate_activation_is_rejected(): void
    {
        $member = $this->makeMember(['status' => 'Active', 'package' => 25, 'p2p_wallet' => 1000]);

        $this->expectException(\RuntimeException::class);
        app(PackageService::class)->activatePackage($member->memberid, 50);
    }

    public function test_blocked_member_cannot_activate_a_package(): void
    {
        $member = $this->makeMember(['status' => 'Blocked', 'p2p_wallet' => 1000]);

        $this->expectException(\RuntimeException::class);
        app(PackageService::class)->activatePackage($member->memberid, 25);
    }

    public function test_insufficient_balance_is_rejected(): void
    {
        $member = $this->makeMember(['p2p_wallet' => 10]);

        $this->expectException(\RuntimeException::class);
        app(PackageService::class)->activatePackage($member->memberid, 25);
    }

    public function test_invalid_package_amount_is_rejected(): void
    {
        $member = $this->makeMember(['p2p_wallet' => 1000]);

        $this->expectException(\RuntimeException::class);
        app(PackageService::class)->activatePackage($member->memberid, 999);
    }

    public function test_direct_referral_reward_is_credited_to_active_sponsor_with_own_package(): void
    {
        $sponsor = $this->makeMember(['status' => 'Active', 'package' => 100, 'income_cap_limit' => 200]);
        $member = $this->makeMember(['sponsorid' => $sponsor->memberid, 'placementid' => $sponsor->memberid, 'position' => 'Left', 'p2p_wallet' => 1000]);

        app(PackageService::class)->activatePackage($member->memberid, 100);

        $sponsor->refresh();
        $this->assertEquals(10, $sponsor->wallet); // 10% of 100
        $this->assertDatabaseHas('direct_incomes', [
            'memberid' => $sponsor->memberid,
            'directid' => $member->memberid,
            'amount' => '10.00000000',
        ]);
    }

    public function test_sponsor_without_a_package_earns_no_direct_reward(): void
    {
        $sponsor = $this->makeMember(['status' => 'Active', 'package' => 0]);
        $member = $this->makeMember(['sponsorid' => $sponsor->memberid, 'placementid' => $sponsor->memberid, 'position' => 'Left', 'p2p_wallet' => 1000]);

        app(PackageService::class)->activatePackage($member->memberid, 100);

        $sponsor->refresh();
        $this->assertEquals(0, $sponsor->wallet);
        $this->assertSame(0, DirectIncome::count());
    }

    public function test_binary_volume_propagates_up_the_placement_chain(): void
    {
        $root = $this->makeMember(['status' => 'Active']);
        $child = $this->makeMember(['sponsorid' => $root->memberid, 'placementid' => $root->memberid, 'position' => 'Left', 'p2p_wallet' => 1000]);

        app(PackageService::class)->activatePackage($child->memberid, 100);

        $root->refresh();
        $this->assertEquals(100, $root->left_amount);
        $this->assertEquals(100, $root->left_balance);
        $this->assertEquals(0, $root->right_amount);
    }
}
