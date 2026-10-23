<?php

namespace App\Services;

use App\Models\MemberDetail;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Wraps the existing walletTransfer()/p2pwalletTransfer() ledger helpers (still used as-is by
 * the rest of the app) with DB::transaction() + row locking, which this codebase has never had.
 *
 * Naming note: the underlying helpers use 'debit'/'credit' in the double-entry sense for an
 * asset account (debit = balance increases, credit = balance decreases) - this service uses the
 * plain-English names instead (credit = add funds, debit = remove funds) and translates between
 * the two internally, so callers don't have to remember the inverted helper convention.
 */
class WalletService
{
    public function creditMainWallet(string $memberid, float $amount, string $label, string $description, ?string $reference = null): MemberDetail
    {
        return DB::transaction(function () use ($memberid, $amount, $label, $description, $reference) {
            $member = MemberDetail::where('memberid', $memberid)->lockForUpdate()->first();
            if (!$member) {
                throw new RuntimeException("Member not found: {$memberid}");
            }

            $balanceBefore = $member->wallet;
            $member->wallet = $balanceBefore + $amount;
            $member->save();

            walletTransfer($memberid, $amount, 'debit', $balanceBefore, $label, $description, $reference);

            return $member;
        });
    }

    public function debitMainWallet(string $memberid, float $amount, string $label, string $description, ?string $reference = null): MemberDetail
    {
        return DB::transaction(function () use ($memberid, $amount, $label, $description, $reference) {
            $member = MemberDetail::where('memberid', $memberid)->lockForUpdate()->first();
            if (!$member) {
                throw new RuntimeException("Member not found: {$memberid}");
            }

            $balanceBefore = $member->wallet;
            if ($amount > $balanceBefore) {
                throw new RuntimeException('Insufficient main wallet balance.');
            }

            $member->wallet = $balanceBefore - $amount;
            $member->save();

            walletTransfer($memberid, $amount, 'credit', $balanceBefore, $label, $description, $reference);

            return $member;
        });
    }

    public function creditFundWallet(string $memberid, float $amount, string $label, string $description, ?string $reference = null): MemberDetail
    {
        return DB::transaction(function () use ($memberid, $amount, $label, $description, $reference) {
            $member = MemberDetail::where('memberid', $memberid)->lockForUpdate()->first();
            if (!$member) {
                throw new RuntimeException("Member not found: {$memberid}");
            }

            $balanceBefore = $member->p2p_wallet;
            $member->p2p_wallet = $balanceBefore + $amount;
            $member->save();

            p2pwalletTransfer($memberid, $amount, 'debit', $balanceBefore, $label, $description, $reference);

            return $member;
        });
    }

    public function debitFundWallet(string $memberid, float $amount, string $label, string $description, ?string $reference = null): MemberDetail
    {
        return DB::transaction(function () use ($memberid, $amount, $label, $description, $reference) {
            $member = MemberDetail::where('memberid', $memberid)->lockForUpdate()->first();
            if (!$member) {
                throw new RuntimeException("Member not found: {$memberid}");
            }

            $balanceBefore = $member->p2p_wallet;
            if ($amount > $balanceBefore) {
                throw new RuntimeException('Insufficient fund wallet balance.');
            }

            $member->p2p_wallet = $balanceBefore - $amount;
            $member->save();

            p2pwalletTransfer($memberid, $amount, 'credit', $balanceBefore, $label, $description, $reference);

            return $member;
        });
    }

    /**
     * P2P transfer from the sender's fund wallet to the receiver's fund wallet.
     *
     * Fee is deducted from the transferred amount (confirmed policy): the sender is debited
     * exactly $amount, the receiver is credited $amount minus the fee, the difference is not
     * moved anywhere - it is simply not passed through (recorded in the ledger text for audit).
     *
     * @return array{reference: string, gross_amount: float, fee: float, net_amount: float}
     */
    public function transferFunds(string $senderMemberid, string $receiverMemberid, float $amount): array
    {
        if (Setting::get('p2p_transfer_status', 'enabled') !== 'enabled') {
            throw new RuntimeException('P2P transfers are temporarily disabled. Please try again later.');
        }

        if ($senderMemberid === $receiverMemberid) {
            throw new RuntimeException('You can not transfer money to your own account.');
        }

        if ($amount <= 0) {
            throw new RuntimeException('Transfer amount must be greater than zero.');
        }

        $feePercentage = (float) Setting::get('p2p_transfer_fee_percentage', 0);
        $fee = round($amount * $feePercentage / 100, 8);
        $netAmount = $amount - $fee;
        $reference = 'P2P-' . Str::upper(Str::random(12));

        return DB::transaction(function () use ($senderMemberid, $receiverMemberid, $amount, $fee, $netAmount, $feePercentage, $reference) {
            // Lock both member rows in a fixed (sorted) order so two concurrent transfers
            // going in opposite directions can never deadlock on each other's locks.
            $orderedIds = [$senderMemberid, $receiverMemberid];
            sort($orderedIds);
            $members = MemberDetail::whereIn('memberid', $orderedIds)
                ->orderBy('memberid')
                ->lockForUpdate()
                ->get()
                ->keyBy('memberid');

            $sender = $members->get($senderMemberid);
            $receiver = $members->get($receiverMemberid);

            if (!$sender) {
                throw new RuntimeException('Your account could not be found.');
            }
            if (!$receiver) {
                throw new RuntimeException('Entered Memberid is either blocked or unavailable.');
            }
            if (in_array($sender->status, ['Blocked', 'Deactive'], true)) {
                throw new RuntimeException('Your account status does not allow P2P transfers. Please contact system admin.');
            }
            if (in_array($receiver->status, ['Blocked', 'Deactive'], true)) {
                throw new RuntimeException('Entered Memberid is either blocked or unavailable.');
            }
            if ($amount > $sender->p2p_wallet) {
                throw new RuntimeException('You do not have enough balance to transfer this amount.');
            }

            $senderBalanceBefore = $sender->p2p_wallet;
            $sender->p2p_wallet = $senderBalanceBefore - $amount;
            $sender->save();

            $receiverBalanceBefore = $receiver->p2p_wallet;
            $receiver->p2p_wallet = $receiverBalanceBefore + $netAmount;
            $receiver->save();

            p2pwalletTransfer(
                $senderMemberid,
                $amount,
                'credit',
                $senderBalanceBefore,
                'P2P Fund Transfer',
                sprintf(
                    '$%s transferred to %s (fee %s%%: $%s, recipient received $%s)',
                    number_format($amount, 2),
                    $receiverMemberid,
                    rtrim(rtrim(number_format($feePercentage, 2), '0'), '.'),
                    number_format($fee, 2),
                    number_format($netAmount, 2)
                ),
                $reference
            );

            p2pwalletTransfer(
                $receiverMemberid,
                $netAmount,
                'debit',
                $receiverBalanceBefore,
                'P2P Fund Transfer',
                sprintf(
                    '$%s received from %s (gross $%s, fee %s%%: $%s)',
                    number_format($netAmount, 2),
                    $senderMemberid,
                    number_format($amount, 2),
                    rtrim(rtrim(number_format($feePercentage, 2), '0'), '.'),
                    number_format($fee, 2)
                ),
                $reference
            );

            return [
                'reference' => $reference,
                'gross_amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
            ];
        });
    }
}
