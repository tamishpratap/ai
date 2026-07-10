<?php

namespace Tests\Feature\Mlm;

use App\Services\WalletService;

class P2pTransferTest extends MlmTestCase
{
    public function test_fee_is_deducted_from_the_transferred_amount(): void
    {
        $sender = $this->makeMember(['status' => 'Active', 'p2p_wallet' => 1000]);
        $receiver = $this->makeMember(['status' => 'Active', 'p2p_wallet' => 0]);

        $result = app(WalletService::class)->transferFunds($sender->memberid, $receiver->memberid, 100);

        $this->assertEquals(100, $result['gross_amount']);
        $this->assertEquals(5, $result['fee']);
        $this->assertEquals(95, $result['net_amount']);

        $sender->refresh();
        $receiver->refresh();
        $this->assertEquals(900, $sender->p2p_wallet); // debited exactly 100
        $this->assertEquals(95, $receiver->p2p_wallet); // credited 95
    }

    public function test_self_transfer_is_rejected(): void
    {
        $member = $this->makeMember(['status' => 'Active', 'p2p_wallet' => 1000]);

        $this->expectException(\RuntimeException::class);
        app(WalletService::class)->transferFunds($member->memberid, $member->memberid, 100);
    }

    public function test_insufficient_balance_is_rejected(): void
    {
        $sender = $this->makeMember(['status' => 'Active', 'p2p_wallet' => 10]);
        $receiver = $this->makeMember(['status' => 'Active', 'p2p_wallet' => 0]);

        $this->expectException(\RuntimeException::class);
        app(WalletService::class)->transferFunds($sender->memberid, $receiver->memberid, 100);
    }

    public function test_blocked_sender_is_rejected(): void
    {
        $sender = $this->makeMember(['status' => 'Blocked', 'p2p_wallet' => 1000]);
        $receiver = $this->makeMember(['status' => 'Active', 'p2p_wallet' => 0]);

        $this->expectException(\RuntimeException::class);
        app(WalletService::class)->transferFunds($sender->memberid, $receiver->memberid, 100);
    }

    public function test_blocked_receiver_is_rejected(): void
    {
        $sender = $this->makeMember(['status' => 'Active', 'p2p_wallet' => 1000]);
        $receiver = $this->makeMember(['status' => 'Blocked', 'p2p_wallet' => 0]);

        $this->expectException(\RuntimeException::class);
        app(WalletService::class)->transferFunds($sender->memberid, $receiver->memberid, 100);
    }
}
