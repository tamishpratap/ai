<?php

namespace Tests\Feature\Mlm;

use App\Models\BinaryIncome;
use App\Services\BinaryService;

class BinaryClosingTest extends MlmTestCase
{
    public function test_matching_credits_10_percent_of_the_weaker_leg_and_carries_the_rest_forward(): void
    {
        $member = $this->makeMember([
            'status' => 'Active',
            'income_cap_limit' => 100000,
        ]);
        $member->left_balance = 5000;
        $member->right_balance = 3000;
        $member->save();

        $result = app(BinaryService::class)->processBinaryClosing('2026-07-10');

        $this->assertSame(1, $result['processed']);

        $member->refresh();
        $this->assertEquals(2000, $member->left_balance); // 5000 - 3000 matched
        $this->assertEquals(0, $member->right_balance);

        $row = BinaryIncome::where('memberid', $member->memberid)->first();
        $this->assertEquals(3000, $row->matching);
        $this->assertEquals(300, $row->total_amount); // 10% of 3000
        $this->assertEquals(300, $row->amount);
        $this->assertEquals(2000, $row->forward_left);
        $this->assertEquals(0, $row->forward_right);
    }

    public function test_closing_twice_for_the_same_date_does_not_credit_twice(): void
    {
        // Partial match (some carry-forward remains) so the member is still eligible for the
        // second run's initial query - proving the *skip* path, not just "nothing left to do".
        $member = $this->makeMember(['status' => 'Active', 'income_cap_limit' => 100000]);
        $member->left_balance = 5000;
        $member->right_balance = 3000;
        $member->save();

        $first = app(BinaryService::class)->processBinaryClosing('2026-07-10');
        $second = app(BinaryService::class)->processBinaryClosing('2026-07-10');

        $this->assertSame(1, $first['processed']);
        $this->assertSame(0, $second['processed']);
        $this->assertSame(1, $second['skipped_already_closed']);
        $this->assertSame(1, BinaryIncome::where('memberid', $member->memberid)->count());
    }

    public function test_matched_income_respects_the_income_cap(): void
    {
        $member = $this->makeMember([
            'status' => 'Active',
            'income_cap_limit' => 50,
            'capped_income_earned' => 45,
        ]);
        $member->left_balance = 1000;
        $member->right_balance = 1000;
        $member->save();

        app(BinaryService::class)->processBinaryClosing('2026-07-10');

        $row = BinaryIncome::where('memberid', $member->memberid)->first();
        $this->assertEquals(100, $row->total_amount); // calculated: 10% of 1000 matched
        $this->assertEquals(5, $row->amount); // allowed: only 5 remaining under the cap
        $this->assertEquals(95, $row->flushout); // rejected due to cap
    }

    public function test_carry_forward_persists_across_multiple_closings(): void
    {
        $member = $this->makeMember(['status' => 'Active', 'income_cap_limit' => 100000]);
        $member->left_balance = 1000; // right side stays empty this round
        $member->right_balance = 0;
        $member->save();

        app(BinaryService::class)->processBinaryClosing('2026-07-10');
        $member->refresh();
        $this->assertEquals(1000, $member->left_balance); // nothing matched, fully carried forward

        // new right-side volume arrives the next day
        $member->right_balance = 400;
        $member->save();

        app(BinaryService::class)->processBinaryClosing('2026-07-11');
        $member->refresh();
        $this->assertEquals(600, $member->left_balance); // 1000 - 400 matched
        $this->assertEquals(0, $member->right_balance);
    }
}
