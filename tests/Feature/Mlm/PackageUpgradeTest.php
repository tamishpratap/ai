<?php

namespace Tests\Feature\Mlm;

use App\Services\PackageService;

class PackageUpgradeTest extends MlmTestCase
{
    public function test_upgrade_charges_full_new_amount_and_resets_cap_to_2x_new_amount(): void
    {
        $member = $this->makeMember([
            'status' => 'Active',
            'package' => 50,
            'p2p_wallet' => 1000,
            'income_cap_limit' => 100,
            'capped_income_earned' => 40,
        ]);

        app(PackageService::class)->upgradePackage($member->memberid, 100);

        $member->refresh();
        $this->assertEquals(100, $member->package);
        $this->assertEquals(900, $member->p2p_wallet); // full 100 charged, not the 50 difference
        $this->assertEquals(200, $member->income_cap_limit); // 2x new package outright
        $this->assertEquals(40, $member->capped_income_earned); // previously-earned income keeps counting
    }

    public function test_downgrade_is_rejected(): void
    {
        $member = $this->makeMember(['status' => 'Active', 'package' => 250, 'p2p_wallet' => 1000]);

        $this->expectException(\RuntimeException::class);
        app(PackageService::class)->upgradePackage($member->memberid, 100);
    }

    public function test_same_tier_is_rejected_as_not_an_upgrade(): void
    {
        $member = $this->makeMember(['status' => 'Active', 'package' => 100, 'p2p_wallet' => 1000]);

        $this->expectException(\RuntimeException::class);
        app(PackageService::class)->upgradePackage($member->memberid, 100);
    }

    public function test_must_activate_before_upgrading(): void
    {
        $member = $this->makeMember(['status' => 'Temp', 'package' => 0, 'p2p_wallet' => 1000]);

        $this->expectException(\RuntimeException::class);
        app(PackageService::class)->upgradePackage($member->memberid, 100);
    }

    public function test_direct_reward_on_upgrade_uses_full_new_amount(): void
    {
        $sponsor = $this->makeMember(['status' => 'Active', 'package' => 500, 'income_cap_limit' => 1000]);
        $member = $this->makeMember([
            'sponsorid' => $sponsor->memberid,
            'status' => 'Active',
            'package' => 50,
            'p2p_wallet' => 1000,
            'income_cap_limit' => 100,
        ]);

        app(PackageService::class)->upgradePackage($member->memberid, 250);

        $sponsor->refresh();
        $this->assertEquals(25, $sponsor->wallet); // 10% of 250 (full new amount), not of the 200 difference
    }
}
