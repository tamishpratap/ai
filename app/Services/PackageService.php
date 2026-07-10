<?php

namespace App\Services;

use App\Models\MemberDetail;
use App\Models\Package;
use App\Models\PackageDetail;
use App\Models\SetRate;
use App\Models\StakingDetail;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Package purchase (first activation) and upgrade.
 *
 * Per confirmed policy: an upgrade is a fresh full-price purchase of the new tier (not just the
 * difference) - direct reward, binary volume, and the income cap are all based on the full new
 * package amount, exactly like a first activation. The only difference from activatePackage() is
 * that the member already has status Active and an existing package on record.
 */
class PackageService
{
    public function activatePackage(string $memberid, float $amount): array
    {
        return DB::transaction(function () use ($memberid, $amount) {
            $member = MemberDetail::where('memberid', $memberid)->lockForUpdate()->first();
            if (!$member) {
                throw new RuntimeException('Member not found.');
            }
            if (in_array($member->status, ['Blocked', 'Deactive'], true)) {
                throw new RuntimeException('Your account status does not allow package purchase. Please contact system admin.');
            }

            $package = Package::where('amount', $amount)->where('is_active', true)->first();
            if (!$package) {
                throw new RuntimeException('Invalid package selected.');
            }
            if ((float) $member->package > 0) {
                throw new RuntimeException('You already have an active package. Use the upgrade option instead.');
            }
            if ($amount > (float) $member->p2p_wallet) {
                throw new RuntimeException('There is no enough balance in your wallet to purchase this package.');
            }

            return $this->purchase($member, $package, isUpgrade: false);
        });
    }

    public function upgradePackage(string $memberid, float $amount): array
    {
        return DB::transaction(function () use ($memberid, $amount) {
            $member = MemberDetail::where('memberid', $memberid)->lockForUpdate()->first();
            if (!$member) {
                throw new RuntimeException('Member not found.');
            }
            if (in_array($member->status, ['Blocked', 'Deactive'], true)) {
                throw new RuntimeException('Your account status does not allow package upgrade. Please contact system admin.');
            }
            if ((float) $member->package <= 0) {
                throw new RuntimeException('Activate a package first before upgrading.');
            }

            $package = Package::where('amount', $amount)->where('is_active', true)->first();
            if (!$package) {
                throw new RuntimeException('Invalid package selected.');
            }
            if ($amount <= (float) $member->package) {
                throw new RuntimeException('You can only upgrade to a higher package than your current one.');
            }
            if ($amount > (float) $member->p2p_wallet) {
                throw new RuntimeException('There is no enough balance in your wallet to upgrade this package.');
            }

            return $this->purchase($member, $package, isUpgrade: true);
        });
    }

    /**
     * Shared activation/upgrade steps. Caller has already validated status, package
     * validity, upgrade-must-be-higher, and available balance, and holds the row lock.
     */
    private function purchase(MemberDetail $member, Package $package, bool $isUpgrade): array
    {
        $memberid = $member->memberid;
        $amount = (float) $package->amount;
        $isFirstActivation = $member->status === 'Temp';
        $sponsorid = $member->sponsorid;

        $balanceBefore = (float) $member->p2p_wallet;
        $member->p2p_wallet = $balanceBefore - $amount;
        $member->self_biz += $amount;
        $member->team_biz += $amount;
        $member->package = $amount;
        $member->income_cap_limit = $amount * 2; // confirmed: fresh 2x cap on the new package amount
        if ($isFirstActivation) {
            $member->status = 'Active';
            $member->activated_at = now();
        }
        $member->save();

        $staking = new StakingDetail();
        $staking->memberid = $memberid;
        $staking->invest_date = now();
        $staking->invest_amount = $amount;
        $staking->package = $package->name;
        $staking->status = 'Active';
        $staking->rate = optional(SetRate::find(1))->roi_rate ?? 0;
        $staking->is_upgrade = $isUpgrade;
        $staking->save();

        $packageDetail = new PackageDetail();
        $packageDetail->txn_date = now();
        $packageDetail->order_id = ($isUpgrade ? 'UPG' : 'OD') . time();
        $packageDetail->memberid = $memberid;
        $packageDetail->package = $package->name;
        $packageDetail->package_type = $isUpgrade ? 'Upgrade' : 'Staking';
        $packageDetail->package_value = (int) $amount;
        $packageDetail->payment_mode = 'Online';
        $packageDetail->status = 'Active';
        $packageDetail->activated_at = now();
        $packageDetail->save();

        p2pwalletTransfer(
            $memberid,
            $amount,
            'credit',
            $balanceBefore,
            $isUpgrade ? 'Package Upgrade' : 'Package Activation',
            ($isUpgrade ? 'Package upgraded to ' : 'Package activated: ') . $package->name . ' ($' . number_format($amount, 2) . ')',
            ($isUpgrade ? 'UPGRADE-' : 'ACTIVATE-') . $staking->id
        );

        if ($isFirstActivation) {
            updateDownline($sponsorid);
            updateUpline($sponsorid, $memberid);
        }
        team_invest_update($sponsorid, $amount);

        app(IncomeService::class)->creditDirectReward(
            $sponsorid,
            $memberid,
            $amount,
            $package->name,
            $isUpgrade ? 'upgrade' : 'activation',
            $staking->id
        );

        app(BinaryService::class)->addBinaryVolume($memberid, $amount);

        return [
            'staking_id' => $staking->id,
            'package' => $package->name,
            'amount' => $amount,
            'is_upgrade' => $isUpgrade,
        ];
    }
}
