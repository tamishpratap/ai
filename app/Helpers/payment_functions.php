<?php

use App\Models\AddonBonusIncome;
use App\Models\DirectIncome;
use App\Models\ImportFund;
use App\Models\MemberDetail;
use App\Models\IbIncome;
use App\Models\RankIncome;
use App\Models\StakingDetail;
use App\Models\StakingIncome;
use App\Models\WalletTransfer;
use App\Models\WithdrawalRequest;
use App\Models\SetRate;
use App\Models\SpecialTradingBonus;
use Illuminate\Support\Facades\Date;

function walletTransfer($memberid, $amount, $type, $wallet, $walletType, $text, $reference = null, $status = 'Completed')
{
    if ($type == 'debit') {
        $ins = new WalletTransfer();
        $ins->memberid = $memberid;
        $ins->walletType = $walletType;
        $ins->debit = $amount;
        $ins->balance_before = $wallet;
        $ins->balance = $wallet + $amount;
        $ins->particular = $text;
        $ins->masterid = $reference;
        $ins->status = $status;
        $ins->save();
    } elseif ($type == 'credit') {
        $ins = new WalletTransfer();
        $ins->memberid = $memberid;
        $ins->walletType = $walletType;
        $ins->credit = $amount;
        $ins->balance_before = $wallet;
        $ins->balance = $wallet - $amount;
        $ins->particular = $text;
        $ins->masterid = $reference;
        $ins->status = $status;
        $ins->save();
    }
}

function p2pwalletTransfer($memberid, $amount, $type, $p2pwallet, $walletType, $text, $reference = null, $status = 'Completed')
{
    if ($type == 'debit') {
        $ins = new WalletTransfer();
        $ins->memberid = $memberid;
        $ins->walletType = $walletType;
        $ins->debit = $amount;
        $ins->balance_before = $p2pwallet;
        $ins->balance = $p2pwallet + $amount;
        $ins->particular = $text;
        $ins->masterid = $reference;
        $ins->status = $status;
        $ins->save();
    } elseif ($type == 'credit') {
        $ins = new WalletTransfer();
        $ins->memberid = $memberid;
        $ins->walletType = $walletType;
        $ins->credit = $amount;
        $ins->balance_before = $p2pwallet;
        $ins->balance = $p2pwallet - $amount;
        $ins->particular = $text;
        $ins->masterid = $reference;
        $ins->status = $status;
        $ins->save();
    }
}

function capping($memberid)
{
    $var = MemberDetail::where('memberid', $memberid)->first();
    $downline = $var->downline;

    if ($downline >= 1) {
        $cap = $var['self_biz'] * 3;
    } else {
        $cap = $var['self_biz'] * 2;
    }

    return $cap;
}

// function stakingCapping($memberid, $staking_id)
// {
//     $sum = StakingDetail::where([['memberid', $memberid], ['id','<=', $staking_id]])->sum('invest_amount');
//     $cap = $sum * 3;
//     return $cap;
// }

function stakingCapping($memberid, $staking_id)
{
    $sum = StakingDetail::where([['memberid', $memberid], ['id','<=', $staking_id]])->sum('invest_amount');
    $var = MemberDetail::where('memberid', $memberid)->first();
    $downline = $var->downline;

    if ($downline >= 1) {
        $cap = $sum * 3;
    } else {
        $cap = $sum * 2;
    }
    return $cap;
}

function Income3xachieved($memberid)
{
    $var =  MemberDetail::where([['memberid', $memberid], ['status', '!=', 'Temp']])->first();
    if ($var) {
        $date = $var['activated_at'];
        $direct = DirectIncome::where([['status', 'Paid'], ['memberid', $memberid], ['created_at', '>=', $date]])->sum('amount');
        $staking = StakingIncome::where([['memberid', $memberid], ['created_at', '>=', $date]])->sum('amount');
        $addon = AddonBonusIncome::where([['status', 'Paid'], ['memberid', $memberid], ['created_at', '>=', $date]])->sum('amount');
        $special = SpecialTradingBonus::where([['status', 'Paid'], ['memberid', $memberid], ['created_at', '>=', $date]])->sum('amount');
        $ib = IbIncome::where([['status', 'Paid'], ['memberid', $memberid], ['created_at', '>=', $date]])->sum('amount');
        
        $totalAmount = $direct + $addon + $staking + $special + $ib;
    } else {
        $totalAmount = 0;
    }
    return $totalAmount;
}



//Direct Income functions
function directIncome($sponsorid, $memberid, $name, $package, $type)
{
    if ($sponsorid != 'Root') {
        $var = MemberDetail::where('memberid', $sponsorid)->first();
        $status = $var->status;
        $sponsor_package = $var->package;

        $direct_amount = $package / 100 * 10;

        if ($status == 'Active') {
            $capping = capping($sponsorid);
            $achieved = Income3xachieved($sponsorid);
            $balance = $capping - $achieved;
            if ($balance <= $direct_amount) {
                $amount = $balance;
            } else {
                $amount = $direct_amount;
            }

            if ($amount > 0) {
                $insert = new DirectIncome();
                $insert->memberid = $sponsorid;
                $insert->amount = $amount;
                $insert->directid = $memberid;
                $insert->name = $name;
                $insert->package = $package;
                $insert->sponsor_package = $sponsor_package;
                $insert->type = $type;
                $insert->status = 'Paid';
                $insert->save();

                $wallet = $var->wallet;
                $var->wallet += $amount;
                $var->save();

                walletTransfer($sponsorid, $amount, 'debit', $wallet, 'Direct Income', 'Direct Income Amount Added into wallet. Payment Id-' . $insert->id);
            }
        }
    }
}

function levelamount($level, $package)
{

    if ($level == 1) {
        $amount = $package / 100 * 0.5;
    } elseif ($level == 2) {
        $amount = $package / 100 * 0.25;
    } elseif ($level == 3) {
        $amount = $package / 100 * 0.25;
    } else {
        $amount = 0;
    }

    return $amount;
}

function getteamMember($userid, $memberid)
{
    $members = MemberDetail::where('sponsorid', $userid)->pluck('memberid')->toArray();
    $total = count($members);
    $array = $members;
    while (count($members) != 0) {
        $members = MemberDetail::whereIn('sponsorid', $members)->pluck('memberid')->toArray();
        $array = array_merge($array, $members);
        $total += count($members);
    }

    if (in_array($memberid, $array)) {
        return 1;
    } else {
        return 0;
    }
}

function activeTeamMember($memberid)
{
    $members = MemberDetail::where('sponsorid', $memberid)->pluck('memberid')->toArray();
    $total = count($members);
    $array = $members;
    while (count($members) != 0) {
        $members = MemberDetail::whereIn('sponsorid', $members)->pluck('memberid')->toArray();
        $array = array_merge($array, $members);
        $total += count($members);
    }
    $count = MemberDetail::whereIn('memberid', $array)->where('status', 'Active')->count();

    return $count;
}

function activeReferralMember($memberid)
{
    $count = MemberDetail::where([['sponsorid', $memberid], ['status', 'Active']])->count();

    return $count;
}

function team_invest_update($sponsorid, $amount)
{
    $limit = 1000;
    for ($i = 1; $i < $limit; $i++) {
        if ($sponsorid != 'Root') {
            $query = MemberDetail::where('memberid', $sponsorid)->first();
            if ($query->status == 'Active') {
                $query->team_biz += $amount;
            }
            $query->save();
            $sponsorid = $query->sponsorid;
        } else {
            break;
        }
    }
}

function dailyIncSetRate($package)
{
    if ($package >= 100 && $package <= 999) {
        $rate = 1.5;
    } elseif ($package >= 1000 && $package <= 4999) {
        $rate = 2;
    } elseif ($package >= 5000 && $package <= 9999) {
        $rate = 2.5;
    } elseif ($package >= 10000 && $package <= 24999) {
        $rate = 3;
    } elseif ($package >= 25000 && $package <= 99999) {
        $rate = 3.5;
    } elseif ($package >= 100000) {
        $rate = 4;
    }
    return $rate;
}

function getUsdgRate()
{
    $rate = SetRate::find(1);
    return $rate->usdg_rate;
}

function importFundCount($memberid)
{
    $count = ImportFund::where([['memberid', $memberid], ['status', 'Approved']])->count();
    return $count;
}

function addOnBonus($sponsorid, $deposit_amount, $count, $memberid)
{
    $rate = 5;
    $Bonusamount = $deposit_amount / 100 * $rate;

    if ($sponsorid == 'Active' ||  $sponsorid != 'Root') {

        $capping = capping($sponsorid);
        $achieved = Income3xachieved($sponsorid);
        $balance = $capping - $achieved;
        if ($balance <= $Bonusamount) {
            $amount = $balance;
        } else {
            $amount = $Bonusamount;
        }

        if ($amount > 0) {
            $var = new AddonBonusIncome();
            $var->memberid = $sponsorid;
            $var->deposit_no = $count;
            $var->deposit_amount = $deposit_amount;
            $var->amount = $amount;
            $var->rate = $rate;
            $var->depositid = $memberid;
            $var->status = 'Paid';
            $var->save();
            
            $mem = MemberDetail::where('memberid', $sponsorid)->first();
            $wallet = $mem->wallet;
            $mem->wallet += $amount;
            $mem->save();

            walletTransfer($sponsorid, $amount, 'debit', $wallet, 'Addon Bonus Income', 'Addon Bonus Income Amount Added into wallet.');
        }
    }
}

function team_roi_update($sponsorid, $value)
{
    $limit = 21;
    for ($i = 1; $i < $limit; $i++) {
        if ($sponsorid != 'Root') {
            $query = MemberDetail::where('memberid', $sponsorid)->first();
            $query->roi_biz += $value;
            $query->save();
            if ($query->reward_status > 0) {
               break;
            }
            $sponsorid = $query->sponsorid;
        } else {
            break;
        }
    }
}

function getTradingRate($rank)
{
    if ($rank == 'Silver') {
        $rate = 15;
    } elseif ($rank == 'Gold') {
        $rate = 30;
    } elseif ($rank == 'Diamond') {
        $rate = 40;
    } else {
        $rate = 40;
    }

    return $rate;
}

function insertSpecialTradingBonus($sponsorid, $team_income, $rate, $memberid, $rank)
{
    $tradeBonus = $team_income / 100 * $rate;

    $capping = capping($sponsorid);
    $achieved = Income3xachieved($sponsorid);
    $balance = $capping - $achieved;
    if ($balance <= $tradeBonus) {
        $amount = $balance;
    } else {
        $amount = $tradeBonus;
    }

    if ($amount > 0) {

        $var = new SpecialTradingBonus();
        $var->memberid = $sponsorid;
        $var->downlineid = $memberid;
        $var->rank = $rank;
        $var->share = $rate;
        $var->team_biz = $team_income;
        $var->amount = $amount;
        $var->status = 'Paid';
        $var->save();

        $qry = MemberDetail::where('memberid', $sponsorid)->first();
        $wallet = $qry->wallet;
        $qry->wallet += $amount;
        $qry->save();

        walletTransfer($sponsorid, $amount, 'debit', $wallet, 'Special Trading Bonus', 'Special Trading Bonus Amount Added into wallet.');
    }
}
