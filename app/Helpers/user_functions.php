<?php

use App\Models\AddonBonusIncome;
use App\Models\FundRequest;
use App\Models\DirectIncome;
use App\Models\IbIncome;
use App\Models\ImportFund;
use App\Models\LevelIncome;
use App\Models\MemberDetail;
use App\Models\RankIncome;
use App\Models\SpecialTradingBonus;
use App\Models\StakingIncome;
use App\Models\TicketText;
use App\Models\UplineMember;
use App\Models\WithdrawalRequest;

function userId()
{
    $userid = "GAB" . rand(100000, 999999);
    $member = MemberDetail::where('memberid', $userid)->count();
    if ($member > 0) {
        userId();
    } else {
        return $userid;
    }
}

function getName($memberid)
{
    $var = MemberDetail::where('memberid', $memberid)->first();
    return $var['name'];
}

function getAllData($memberid)
{
    $var = MemberDetail::where('memberid', $memberid)->first();
    return $var;
}

function AdmUnread($ticketid)
{
    $count = TicketText::where([['ticket_id', $ticketid], ['written_by', 'Admin'], ['status', 'Unread']])->count();
    if ($count > 0) {
        return $count;
    } else {
        return 0;
    }
}
function userUnread($ticketid)
{
    $count = TicketText::where([['ticket_id', $ticketid], ['written_by', 'Member'], ['status', 'Unread']])->count();
    if ($count > 0) {
        return $count;
    } else {
        return 0;
    }
}

function updateUpline($sponsorid, $memberid)
{
    if ($memberid != 'GAB123456') {
        $pool = UplineMember::where('memberid', $sponsorid)->first();
        $var = new UplineMember();
        $var->memberid = $memberid;
        $var->upline_1 = $pool->memberid;
        $var->upline_2 = $pool->upline_1;
        $var->upline_3 = $pool->upline_2;
        $var->upline_4 = $pool->upline_3;
        $var->upline_5 = $pool->upline_4;
        $var->upline_6 = $pool->upline_5;
        $var->upline_7 = $pool->upline_6;
        $var->upline_8 = $pool->upline_7;
        $var->upline_9 = $pool->upline_8;
        $var->upline_10 = $pool->upline_9;
        $var->save();
    }
}

function totalsingleLevel($memberid, $level)
{
    $upline = 'upline_' . $level;
    $var = UplineMember::where($upline, $memberid)->count();
    return $var;
}

function updateDownline($sponsorid)
{
    if ($sponsorid != 'Root') {
        $var = MemberDetail::where('memberid', $sponsorid)->first();
        $var->downline += 1;
        $var->save();
    }
}

function todayWithdrawals($memberid)
{
    $var = WithdrawalRequest::where([['memberid', $memberid], ['status', 'Approved']])->whereDate('created_at', date('Y-m-d'))->sum('gross_amount');
    return $var;
}

function totalWithdrawals($memberid)
{
    $var = WithdrawalRequest::where([['memberid', $memberid], ['status', 'Approved']])->sum('gross_amount');
    return $var;
}

function totalDeposit($memberid)
{
    $var = ImportFund::where([['memberid', $memberid], ['status', 'Approved'], ['added_by', 'User']])->sum('amount');
    return $var;
}

function totalIncome($memberid)
{
    $total = totalStakingIncome($memberid) + totalLevelIncome($memberid);
    return $total;
}

function totalStakingIncome($memberid)
{
    $var = StakingIncome::where([['status', 'Paid'], ['memberid', $memberid]])->sum('amount');
    return $var;
}

function totalLevelIncome($memberid)
{
    $var = LevelIncome::where([['status', 'Paid'], ['memberid', $memberid]])->sum('amount');
    return $var;
}

function totalDownlines($memberid)
{
    $members = MemberDetail::where('sponsorid', $memberid)->count();
    return $members;
}

function getSponsorid($memberid)
{
    $member = MemberDetail::where('memberid', $memberid)->first();
    return $member['sponsorid'];
}

function powerLegBiz($memberid)
{
    $first = MemberDetail::where([['sponsorid', $memberid], ['status', 'Active']])->orderBy('team_biz', 'DESC')->first();
    if ($first) {
        $first_biz = $first->team_biz;
    } else {
        $first_biz = 0;
    }

    return $first_biz;
}

function otherLegBiz($memberid)
{
    $first = MemberDetail::where([['sponsorid', $memberid], ['status', 'Active']])->orderBy('team_biz', 'DESC')->first();
    if ($first) {
        $first_biz = $first->team_biz;
        $total = MemberDetail::where([['sponsorid', $memberid], ['status', 'Active']])->sum('team_biz');
        $other_biz = $total - $first_biz;
    } else {
        $other_biz = 0;
    }

    return $other_biz;
}

function totalLevelMembers($memberid)
{
    $members = MemberDetail::where('sponsorid', $memberid)->pluck('memberid');
    $total = count($members);
    while (count($members) != 0) {
        $members = MemberDetail::whereIn('sponsorid', $members)->pluck('memberid');
        $total += count($members);
    }

    return $total;
}

function checkTxnid($txnid)
{
    $check = ImportFund::where('txnid', $txnid)->first();
    if (!$check) {
        $check2 = FundRequest::where('tran_id', $txnid)->first();
        if (!$check2) {
            $count = 0;
        } else {
            $count = 1;
        }
    } else {
        $count = 1;
    }
    return $count;
}



function addPlacementId($userid, $sponsor, $position)
{
    if ($position == 'Left') {

        $left = MemberDetail::where('memberid', $sponsor)->first();
        if ($left['left_id'] == NULL) {
            $left->left_id = $userid;
            $left->save();
            $userUpdate = MemberDetail::where('memberid', $userid)->update(['placementid' => $sponsor]);
        } else {
            addPlacementId($userid, $left['left_id'], $position);
        }
    } elseif ($position == 'Right') {
        $right = MemberDetail::where('memberid', $sponsor)->first();
        if ($right['right_id'] == NULL) {
            $right->right_id = $userid;
            $right->save();
            $userUpdate = MemberDetail::where('memberid', $userid)->update(['placementid' => $sponsor]);
        } else {
            addPlacementId($userid, $right['right_id'], $position);
        }
    }
}