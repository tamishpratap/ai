<?php

namespace App\Services;

use App\Models\MemberDetail;
use App\Models\Setting;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WithdrawalService
{
    /**
     * Creates a pending withdrawal request and holds (debits) the full gross amount from the
     * member's main wallet immediately - matches the existing app's behavior of debiting at
     * request time rather than at approval time. The fee is calculated and shown, but only the
     * net_amount would actually be paid out externally; if rejected, the full gross amount held
     * is refunded.
     */
    public function requestWithdrawal(string $memberid, float $amount): array
    {
        return DB::transaction(function () use ($memberid, $amount) {
            $member = MemberDetail::where('memberid', $memberid)->lockForUpdate()->first();
            if (!$member) {
                throw new RuntimeException('Member not found.');
            }
            if (in_array($member->status, ['Blocked', 'Deactive'], true)) {
                throw new RuntimeException('Your account status does not allow withdrawals. Please contact system admin.');
            }
            if (empty($member->wallet_address)) {
                throw new RuntimeException('Your wallet address is not completed. Please fill your wallet address before withdrawal.');
            }

            $hasPending = WithdrawalRequest::where('memberid', $memberid)->where('status', 'Pending')->exists();
            if ($hasPending) {
                throw new RuntimeException('Your Last Withdrawal Still Pending. Please Wait.');
            }

            $minimumWithdrawal = (float) Setting::get('minimum_withdrawal', 20);
            if ($amount < $minimumWithdrawal) {
                throw new RuntimeException("Invalid Amount Entered. Amount must be at least \${$minimumWithdrawal}.");
            }

            $balanceBefore = (float) $member->wallet;
            if ($amount > $balanceBefore) {
                throw new RuntimeException('Invalid Amount Entered. Amount can not be greater than wallet balance.');
            }

            $feePercentage = (float) Setting::get('withdrawal_fee_percentage', 10);
            $fee = round($amount * $feePercentage / 100, 8);
            $netAmount = $amount - $fee;

            $member->wallet = $balanceBefore - $amount;
            $member->save();

            // "RQ".time() alone can collide - two requests within the same second get the same
            // id (confirmed while testing this service) since request_id has no unique DB index.
            $requestId = 'RQ' . time() . strtoupper(Str::random(6));

            $withdrawal = new WithdrawalRequest();
            $withdrawal->request_date = now();
            $withdrawal->request_id = $requestId;
            $withdrawal->memberid = $memberid;
            $withdrawal->name = $member->name;
            $withdrawal->type = 'Online';
            $withdrawal->wallet_address = $member->wallet_address;
            $withdrawal->gross_amount = $amount;
            $withdrawal->service_charge = $fee;
            $withdrawal->net_amount = $netAmount;
            $withdrawal->status = 'Pending';
            $withdrawal->save();

            walletTransfer(
                $memberid,
                $amount,
                'credit',
                $balanceBefore,
                'Withdrawal',
                sprintf(
                    '$%s withdrawal requested (fee %s%%: $%s, net payable $%s). Request ID: %s',
                    number_format($amount, 2),
                    rtrim(rtrim(number_format($feePercentage, 2), '0'), '.'),
                    number_format($fee, 2),
                    number_format($netAmount, 2),
                    $requestId
                ),
                $requestId
            );

            return [
                'request_id' => $requestId,
                'gross_amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
            ];
        });
    }

    /**
     * Admin approval - a completed off-chain/manual payment, tx hash pasted in afterward.
     * No private key ever touches this application.
     */
    public function approveWithdrawal(int $withdrawalId, string $transactionHash, ?string $comment): WithdrawalRequest
    {
        return DB::transaction(function () use ($withdrawalId, $transactionHash, $comment) {
            $withdrawal = WithdrawalRequest::where('id', $withdrawalId)->lockForUpdate()->first();
            if (!$withdrawal) {
                throw new RuntimeException('Withdrawal request not found.');
            }
            if ($withdrawal->status !== 'Pending') {
                throw new RuntimeException('This withdrawal request has already been processed.');
            }

            $withdrawal->txnid = $transactionHash;
            $withdrawal->admin_comment = $comment;
            $withdrawal->status = 'Approved';
            $withdrawal->payment_date = now();
            $withdrawal->save();

            return $withdrawal;
        });
    }

    public function rejectWithdrawal(int $withdrawalId, ?string $comment): WithdrawalRequest
    {
        return DB::transaction(function () use ($withdrawalId, $comment) {
            $withdrawal = WithdrawalRequest::where('id', $withdrawalId)->lockForUpdate()->first();
            if (!$withdrawal) {
                throw new RuntimeException('Withdrawal request not found.');
            }
            if ($withdrawal->status !== 'Pending') {
                throw new RuntimeException('This withdrawal request has already been processed.');
            }

            $withdrawal->admin_comment = $comment;
            $withdrawal->status = 'Cancelled';
            $withdrawal->save();

            $member = MemberDetail::where('memberid', $withdrawal->memberid)->lockForUpdate()->first();
            if ($member) {
                $balanceBefore = (float) $member->wallet;
                $member->wallet = $balanceBefore + (float) $withdrawal->gross_amount;
                $member->save();

                walletTransfer(
                    $withdrawal->memberid,
                    (float) $withdrawal->gross_amount,
                    'debit',
                    $balanceBefore,
                    'Withdrawal Cancelled',
                    sprintf(
                        '$%s refunded to wallet - withdrawal request %s rejected',
                        number_format((float) $withdrawal->gross_amount, 2),
                        $withdrawal->request_id
                    ),
                    $withdrawal->request_id
                );
            }

            return $withdrawal;
        });
    }
}
