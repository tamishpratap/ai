<?php

namespace Tests\Feature\Mlm;

use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;

class WithdrawalTest extends MlmTestCase
{
    public function test_request_below_minimum_is_rejected(): void
    {
        $member = $this->makeMember(['status' => 'Active', 'wallet' => 1000]);

        $this->expectException(\RuntimeException::class);
        app(WithdrawalService::class)->requestWithdrawal($member->memberid, 5);
    }

    public function test_request_holds_gross_amount_and_computes_10_percent_fee(): void
    {
        $member = $this->makeMember(['status' => 'Active', 'wallet' => 1000]);

        $result = app(WithdrawalService::class)->requestWithdrawal($member->memberid, 100);

        $this->assertEquals(100, $result['gross_amount']);
        $this->assertEquals(10, $result['fee']);
        $this->assertEquals(90, $result['net_amount']);

        $member->refresh();
        $this->assertEquals(900, $member->wallet); // full gross held
    }

    public function test_second_request_blocked_while_one_is_pending(): void
    {
        $member = $this->makeMember(['status' => 'Active', 'wallet' => 1000]);
        app(WithdrawalService::class)->requestWithdrawal($member->memberid, 100);

        $this->expectException(\RuntimeException::class);
        app(WithdrawalService::class)->requestWithdrawal($member->memberid, 50);
    }

    public function test_approve_then_double_approve_fails(): void
    {
        $member = $this->makeMember(['status' => 'Active', 'wallet' => 1000]);
        $result = app(WithdrawalService::class)->requestWithdrawal($member->memberid, 100);
        $withdrawal = WithdrawalRequest::where('request_id', $result['request_id'])->first();

        app(WithdrawalService::class)->approveWithdrawal($withdrawal->id, '0xHASH', 'paid');
        $withdrawal->refresh();
        $this->assertSame('Approved', $withdrawal->status);
        $this->assertSame('0xHASH', $withdrawal->txnid);

        $this->expectException(\RuntimeException::class);
        app(WithdrawalService::class)->approveWithdrawal($withdrawal->id, '0xAGAIN', null);
    }

    public function test_reject_refunds_the_full_held_amount(): void
    {
        $member = $this->makeMember(['status' => 'Active', 'wallet' => 1000]);
        $result = app(WithdrawalService::class)->requestWithdrawal($member->memberid, 100);
        $withdrawal = WithdrawalRequest::where('request_id', $result['request_id'])->first();

        app(WithdrawalService::class)->rejectWithdrawal($withdrawal->id, 'not valid');

        $member->refresh();
        $this->assertEquals(1000, $member->wallet); // fully refunded

        $this->expectException(\RuntimeException::class);
        app(WithdrawalService::class)->rejectWithdrawal($withdrawal->id, 'again');
    }

    public function test_blocked_member_cannot_request_withdrawal(): void
    {
        $member = $this->makeMember(['status' => 'Blocked', 'wallet' => 1000]);

        $this->expectException(\RuntimeException::class);
        app(WithdrawalService::class)->requestWithdrawal($member->memberid, 100);
    }

    public function test_missing_wallet_address_is_rejected(): void
    {
        $member = $this->makeMember(['status' => 'Active', 'wallet' => 1000]);
        $member->wallet_address = null;
        $member->save();

        $this->expectException(\RuntimeException::class);
        app(WithdrawalService::class)->requestWithdrawal($member->memberid, 100);
    }
}
