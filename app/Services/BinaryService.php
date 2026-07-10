<?php

namespace App\Services;

use App\Models\BinaryIncome;
use App\Models\MemberDetail;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Binary business volume + matching bonus.
 *
 * member_details already has four unused int columns that this service reuses directly instead
 * of adding new ones: left_amount/right_amount (lifetime volume, for reporting - never decreases)
 * and left_balance/right_balance (currently available/unmatched volume - the working carry-forward
 * pool, decreased only by processBinaryClosing() when matched).
 *
 * binary_incomes already has a full matching-bonus ledger schema (never written to until this
 * service): left_forwarded/right_forwarded (carried in from the previous closing), left_new/
 * right_new (volume added since then), total_left/total_right (forwarded + new), matching
 * (matched volume), total_amount (calculated bonus before the cap), flushout (rejected due to
 * cap), amount (actually credited), forward_left/forward_right (unmatched remainder carried to
 * the next closing).
 */
class BinaryService
{
    public function __construct(private IncomeService $incomeService)
    {
    }

    /**
     * Walks up the binary placement chain from a member (the one whose package just activated
     * or upgraded) to 'Root', adding $amount as business volume to whichever leg (left/right)
     * that member sits under, for every ancestor. Mirrors binary_count_update()'s traversal
     * (app/Helpers/common_function.php) but adds a currency amount instead of +1 to a count.
     */
    public function addBinaryVolume(string $memberid, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($memberid, $amount) {
            $member = MemberDetail::where('memberid', $memberid)->first();
            if (!$member) {
                throw new RuntimeException("Member not found: {$memberid}");
            }

            $placementid = $member->placementid;
            $position = $member->position;

            while ($placementid !== null && $placementid !== 'Root') {
                $ancestor = MemberDetail::where('memberid', $placementid)->lockForUpdate()->first();
                if (!$ancestor) {
                    break;
                }

                if ($position === 'Left') {
                    $ancestor->left_amount += $amount;
                    $ancestor->left_balance += $amount;
                } elseif ($position === 'Right') {
                    $ancestor->right_amount += $amount;
                    $ancestor->right_balance += $amount;
                }
                $ancestor->save();

                $position = $ancestor->position;
                $placementid = $ancestor->placementid;
            }
        });
    }

    /**
     * Runs the daily binary matching closing for every member with unmatched volume on either
     * leg. Idempotent: guarded by the unique (memberid, closing_date) index on binary_incomes,
     * so running this twice for the same date credits nothing twice.
     *
     * @return array{processed: int, skipped_already_closed: int, total_credited: string}
     */
    public function processBinaryClosing(?string $closingDate = null): array
    {
        $closingDate ??= now()->toDateString();

        if (Setting::get('binary_closing_status', 'enabled') !== 'enabled') {
            return ['processed' => 0, 'skipped_already_closed' => 0, 'total_credited' => '0.00000000'];
        }

        $percentage = (float) Setting::get('binary_matching_percentage', 10);

        $memberIds = MemberDetail::where('status', 'Active')
            ->where(function ($query) {
                $query->where('left_balance', '>', 0)->orWhere('right_balance', '>', 0);
            })
            ->pluck('memberid');

        $processed = 0;
        $skipped = 0;
        $totalCredited = 0.0;

        foreach ($memberIds as $memberid) {
            $result = DB::transaction(function () use ($memberid, $closingDate, $percentage) {
                // whereDate() (not where()) because closing_date is cast to 'date' on the model,
                // which normalizes it to a full datetime string on save - a raw string-equality
                // where() against the plain 'Y-m-d' input would never match, silently defeating
                // this idempotency check (confirmed while testing: it let a duplicate row through
                // and only the unique index caught it).
                if (BinaryIncome::where('memberid', $memberid)->whereDate('closing_date', $closingDate)->exists()) {
                    return null;
                }

                $member = MemberDetail::where('memberid', $memberid)->lockForUpdate()->first();
                if (!$member || $member->status !== 'Active') {
                    return null;
                }

                $previous = BinaryIncome::where('memberid', $memberid)->orderByDesc('closing_date')->first();
                $leftForwarded = $previous->forward_left ?? 0;
                $rightForwarded = $previous->forward_right ?? 0;

                $totalLeft = (float) $member->left_balance;
                $totalRight = (float) $member->right_balance;
                $leftNew = max(0, $totalLeft - $leftForwarded);
                $rightNew = max(0, $totalRight - $rightForwarded);

                $matched = min($totalLeft, $totalRight);
                $calculatedAmount = round($matched * $percentage / 100, 8);

                $cap = $this->incomeService->applyIncomeCap($member, $calculatedAmount, 'binary_matching');

                $forwardLeft = $totalLeft - $matched;
                $forwardRight = $totalRight - $matched;

                $member->left_balance = $forwardLeft;
                $member->right_balance = $forwardRight;
                $member->save();

                BinaryIncome::create([
                    'memberid' => $memberid,
                    'closing_date' => $closingDate,
                    'left_forwarded' => $leftForwarded,
                    'left_new' => $leftNew,
                    'total_left' => $totalLeft,
                    'right_forwarded' => $rightForwarded,
                    'right_new' => $rightNew,
                    'total_right' => $totalRight,
                    'matching' => $matched,
                    'total_amount' => $calculatedAmount,
                    'flushout' => $cap['rejected_amount'],
                    'amount' => $cap['allowed_amount'],
                    'forward_left' => $forwardLeft,
                    'forward_right' => $forwardRight,
                    'installments' => ($previous->installments ?? 0) + 1,
                    'status' => 'Paid',
                ]);

                if ($cap['allowed_amount'] > 0) {
                    $walletService = new WalletService();
                    $walletService->creditMainWallet(
                        $memberid,
                        $cap['allowed_amount'],
                        'Binary Matching Income',
                        sprintf(
                            'Binary matching income for %s: matched $%s @ %s%% = $%s (credited $%s)',
                            $closingDate,
                            number_format($matched, 2),
                            rtrim(rtrim(number_format($percentage, 2), '0'), '.'),
                            number_format($calculatedAmount, 2),
                            number_format($cap['allowed_amount'], 2)
                        ),
                        'BINARY-' . $memberid . '-' . $closingDate
                    );
                }

                return $cap['allowed_amount'];
            });

            if ($result === null) {
                $skipped++;
            } else {
                $processed++;
                $totalCredited += (float) $result;
            }
        }

        return [
            'processed' => $processed,
            'skipped_already_closed' => $skipped,
            'total_credited' => number_format($totalCredited, 8, '.', ''),
        ];
    }
}
