<?php

use App\Models\AddonBonusIncome;
use App\Models\MemberDetail;
use App\Models\ImportFund;
use App\Models\DirectIncome;
use App\Models\IbIncome;
use App\Models\LevelIncome;
use App\Models\RewardIncome;
use App\Models\SpecialTradingBonus;
use App\Models\StakingDetail;
use App\Models\StakingIncome;
use App\Models\WithdrawalRequest;

function totalMembersadmin()
{
    $count = MemberDetail::all();
    return count($count);
}

function totalActiveMembers()
{
    $count = MemberDetail::where('status', 'Active')->get();
    return count($count);
}

function totalTempMembers()
{
    $count = MemberDetail::where('status', 'Temp')->count();
    return $count;
}

function totalblockedMembers()
{
    $count = MemberDetail::where('status', 'Blocked')->count();
    return $count;
}

function todayStacking()
{
    $var = StakingDetail::whereDate('created_at', date('Y-m-d'))->sum('invest_amount');
    return $var;
}

function totalStaking()
{
    $var = StakingDetail::sum('invest_amount');
    return $var;
}

function todayPackages()
{
    $var = MemberDetail::whereDate('created_at', date('Y-m-d'))->sum('package');
    return $var;
}

function totalPackages()
{
    $var = MemberDetail::sum('package');
    return $var;
}

function todaysWithdrawal()
{
    $sum = WithdrawalRequest::where([['status', 'Approved'], ['payment_date', date('Y-m-d')]])->sum('gross_amount');
    return $sum;
}

function totalWithdrawal()
{
    $sum = WithdrawalRequest::where('status', 'Approved')->sum('gross_amount');
    return $sum;
}

function TodayinWallet()
{
    $sum = MemberDetail::where('status', 'Active')->whereDate('created_at', date('Y-m-d'))->sum('wallet');
    return $sum;
}

function totalinWallet()
{
    $sum = MemberDetail::where('status', 'Active')->sum('wallet');
    return $sum;
}

function totalinPrincipalWallet()
{
    $sum = MemberDetail::where('status', 'Active')->sum('principal_wallet');
    return $sum;
}

function TodayinFundWallet()
{
    $sum = MemberDetail::where('status', 'Active')->whereDate('created_at', date('Y-m-d'))->sum('p2p_wallet');
    return $sum;
}

function totalinFundWallet()
{
    $sum = MemberDetail::where('status', 'Active')->sum('p2p_wallet');
    return $sum;
}

function totalInc()
{
    $total = totalLevelInc() + totalStakingInc() + totalRewardInc() + totalDirectInc();
    return $total;
}
function totalLevelInc()
{
    $var = LevelIncome::where('status', 'Paid')->sum('amount');
    return $var;
}

function totalStakingInc()
{
    $var = StakingIncome::where('status', 'Paid')->sum('amount');
    return $var;
}


function totalRewardInc()
{
    $var = RewardIncome::where('status', 'Paid')->sum('amount');
    return $var;
}


function totalDirectInc()
{
    $var = DirectIncome::where('status', 'Paid')->sum('amount');
    return $var;
}


function totalgrossAmount()
{
    $count = WithdrawalRequest::where('status', 'Approved')->sum('gross_amount');
    return $count;
}

function totalnetAmount()
{
    $count = WithdrawalRequest::where('status', 'Approved')->sum('net_amount');
    return $count;
}

function totaldeductions()
{
    $count = WithdrawalRequest::where('status', 'Approved')->sum('service_charge');
    return $count;
}

function totalFundImported()
{
    $count = ImportFund::where('status', 'Approved')->sum('amount');
    return $count;
}

function todaysFundImported()
{
    $count = ImportFund::where('status', 'Approved')->whereDate('created_at', date('Y-m-d'))->sum('amount');
    return $count;
}

// function todayInc()
// {
//     $total = todayLevelInc() + todayDailyLevelInc() + todayStackingInc() + todayClubInc();
//     return $total;
// }

function todayStackingInc()
{
    $var = StakingIncome::where('status', 'Paid')->whereDate('created_at', date('Y-m-d'))->sum('amount');
    return $var;
}



