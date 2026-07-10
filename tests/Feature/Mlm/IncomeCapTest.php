<?php

namespace Tests\Feature\Mlm;

use App\Models\StakingDetail;
use App\Services\IncomeService;

class IncomeCapTest extends MlmTestCase
{
    public function test_income_is_clamped_to_remaining_cap(): void
    {
        $member = $this->makeMember(['income_cap_limit' => 400, 'capped_income_earned' => 395]);

        $result = app(IncomeService::class)->applyIncomeCap($member, 7.5, 'trading');

        $this->assertEquals(5, $result['allowed_amount']);
        $this->assertEquals(2.5, $result['rejected_amount']);
        $this->assertTrue($result['cap_reached']);
        $member->refresh();
        $this->assertEquals(400, $member->capped_income_earned);
    }

    public function test_income_under_the_cap_is_fully_allowed(): void
    {
        $member = $this->makeMember(['income_cap_limit' => 400, 'capped_income_earned' => 100]);

        $result = app(IncomeService::class)->applyIncomeCap($member, 50, 'trading');

        $this->assertEquals(50, $result['allowed_amount']);
        $this->assertEquals(0, $result['rejected_amount']);
        $this->assertFalse($result['cap_reached']);
    }

    public function test_reaching_the_cap_deactivates_the_active_staking(): void
    {
        $member = $this->makeMember(['income_cap_limit' => 100, 'capped_income_earned' => 90]);
        $staking = new StakingDetail();
        $staking->memberid = $member->memberid;
        $staking->invest_date = now();
        $staking->invest_amount = 50;
        $staking->package = 'Basic';
        $staking->status = 'Active';
        $staking->save();

        app(IncomeService::class)->applyIncomeCap($member, 20, 'trading');

        $staking->refresh();
        $this->assertSame('Deactive', $staking->status);
    }

    public function test_income_already_at_cap_allows_nothing_further(): void
    {
        $member = $this->makeMember(['income_cap_limit' => 100, 'capped_income_earned' => 100]);

        $result = app(IncomeService::class)->applyIncomeCap($member, 10, 'binary_matching');

        $this->assertEquals(0, $result['allowed_amount']);
        $this->assertEquals(10, $result['rejected_amount']);
    }
}
