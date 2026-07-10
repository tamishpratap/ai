<?php

namespace App\Services;

use App\Models\DirectIncome;
use App\Models\MemberDetail;
use App\Models\Setting;
use App\Models\StakingDetail;
use Illuminate\Support\Facades\DB;

class IncomeService
{
    /**
     * The single reusable flat 2x income cap check, covering trading + direct + binary income
     * together (confirmed policy - level_incomes/addon_bonus_incomes/special_trading_bonuses are
     * NOT covered by this cap, they're untouched legacy features).
     *
     * Mutates and saves $member->capped_income_earned in place (the caller is expected to already
     * hold a row lock on $member within its own transaction - this method does not open one, so
     * it composes safely whether called standalone or from inside BinaryService/DailyIncomeDis).
     *
     * When the cap is newly reached, the member's active staking_details row(s) are marked
     * Deactive (same convention DailyIncomeDis already uses for its own 200-day ROI limit) so no
     * further income of any of the 3 capped types gets generated for this package.
     *
     * @return array{calculated_amount: float, allowed_amount: float, rejected_amount: float, previous_eligible_income: float, remaining_cap: float, cap_reached: bool}
     */
    public function applyIncomeCap(MemberDetail $member, float $calculatedAmount, string $incomeType): array
    {
        $previousEarned = (float) $member->capped_income_earned;
        $capLimit = (float) $member->income_cap_limit;
        $remaining = max(0, $capLimit - $previousEarned);
        $allowed = min(max(0, $calculatedAmount), $remaining);
        $rejected = max(0, $calculatedAmount) - $allowed;

        $capReachedNow = false;
        if ($allowed > 0) {
            $member->capped_income_earned = $previousEarned + $allowed;
            $member->save();

            if ($capLimit > 0 && $member->capped_income_earned >= $capLimit && $previousEarned < $capLimit) {
                $capReachedNow = true;
            }
        }

        if ($capReachedNow) {
            StakingDetail::where('memberid', $member->memberid)
                ->where('status', 'Active')
                ->update(['status' => 'Deactive']);
        }

        return [
            'calculated_amount' => max(0, $calculatedAmount),
            'allowed_amount' => $allowed,
            'rejected_amount' => $rejected,
            'previous_eligible_income' => $previousEarned,
            'remaining_cap' => $remaining,
            'cap_reached' => $capLimit > 0 && ($previousEarned + $allowed) >= $capLimit,
        ];
    }

    /**
     * One-shot 10% direct referral reward, credited when a personally sponsored member's package
     * is activated or upgraded. Idempotent via a unique reference tied to the specific staking
     * event (source_type + source_id), so the same activation/upgrade can never pay this twice.
     *
     * Returns null (and credits nothing) when: there is no sponsor (root), the sponsor can't be
     * found, the sponsor isn't Active, the sponsor has no package of their own, the sponsor is
     * already fully capped, or the reference was already paid.
     */
    public function creditDirectReward(
        string $sponsorMemberid,
        string $directMemberid,
        float $packageAmount,
        string $packageLabel,
        string $sourceType,
        int $sourceId
    ): ?array {
        if ($sponsorMemberid === 'Root' || $packageAmount <= 0) {
            return null;
        }

        $reference = "DIRECT-{$sourceType}-{$sourceId}";
        if (DirectIncome::where('reference', $reference)->exists()) {
            return null;
        }

        return DB::transaction(function () use ($sponsorMemberid, $directMemberid, $packageAmount, $packageLabel, $sourceType, $sourceId, $reference) {
            $sponsor = MemberDetail::where('memberid', $sponsorMemberid)->lockForUpdate()->first();
            if (!$sponsor || $sponsor->status !== 'Active') {
                return null;
            }

            // Sponsor must hold an active, non-capped package of their own (confirmed policy).
            if ((float) $sponsor->package <= 0) {
                return null;
            }
            if ((float) $sponsor->income_cap_limit > 0 && (float) $sponsor->capped_income_earned >= (float) $sponsor->income_cap_limit) {
                return null;
            }

            $percentage = (float) Setting::get('direct_referral_percentage', 10);
            $calculatedAmount = round($packageAmount * $percentage / 100, 8);
            if ($calculatedAmount <= 0) {
                return null;
            }

            $cap = $this->applyIncomeCap($sponsor, $calculatedAmount, 'direct_referral');

            DirectIncome::create([
                'memberid' => $sponsorMemberid,
                'directid' => $directMemberid,
                'package' => $packageLabel,
                'amount' => $cap['allowed_amount'],
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'reference' => $reference,
                'status' => 'Paid',
            ]);

            if ($cap['allowed_amount'] > 0) {
                (new WalletService())->creditMainWallet(
                    $sponsorMemberid,
                    $cap['allowed_amount'],
                    'Direct Income',
                    sprintf(
                        'Direct referral reward: %s%% of $%s package (%s) activated by %s (credited $%s)',
                        rtrim(rtrim(number_format($percentage, 2), '0'), '.'),
                        number_format($packageAmount, 2),
                        $packageLabel,
                        $directMemberid,
                        number_format($cap['allowed_amount'], 2)
                    ),
                    $reference
                );
            }

            return $cap;
        });
    }
}
