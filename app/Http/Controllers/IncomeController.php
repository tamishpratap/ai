<?php

namespace App\Http\Controllers;

use App\Models\BinaryIncome;
use App\Models\DirectIncome;
use App\Models\IbIncome;
use App\Models\StakingIncome;
use App\Models\SpecialTradingBonus;
use App\Models\AddonBonusIncome;
use App\Models\LevelIncome;
use App\Models\MemberDetail;
use App\Models\RankIncome;
use App\Models\StakingDetail;
use Illuminate\Http\Request;

class IncomeController extends Controller
{

    public function levelDetails()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['sdata'] = StakingDetail::where([['memberid', $result['data']['memberid']], ['type', 'Level Income']])->orderby('created_at', 'desc')->get();
        return view('member.income.level-details')->with($result);
    }

    public function levelIncomes()
    {
        $result['data'] = MemberDetail::where('memberid', session('MEMBER_ID'))->first();
        $result['levelData'] = LevelIncome::where([['memberid', $result['data']['memberid']], ['status', 'Paid']])->orderby('created_at', 'desc')->get();
        return view('member.income.level-incomes')->with($result);
    }
}
