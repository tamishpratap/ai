<?php

namespace App\Http\Controllers;

use App\Models\BinaryIncome;
use App\Models\DailyIncome;
use App\Models\DirectIncome;
use App\Models\IbIncome;
use App\Models\StakingIncome;
use App\Models\SpecialTradingBonus;
use App\Models\AddonBonusIncome;
use App\Models\LevelIncome;
use App\Models\MemberDetail;
use App\Models\RewardIncome;
use App\Models\StakingDetail;
use Illuminate\Http\Request;

class IncomeController extends Controller
{

   public function rewardDetails()
    {
        $result['data'] = MemberDetail::where('memberid', session('MEMBER_ID'))->first();
        return view('member.income.reward-details')->with($result);
    }

    public function levelIncomes()
    {
        $result['data'] = MemberDetail::where('memberid', session('MEMBER_ID'))->first();
        $result['levelData'] = LevelIncome::where([['memberid', $result['data']['memberid']], ['status', 'Paid']])->orderby('created_at', 'desc')->get();
        return view('member.income.level-incomes')->with($result);
    }
    
    
     public function rewardIncomes()
    {
        $result['data'] = MemberDetail::where('memberid', session('MEMBER_ID'))->first();
        $result['rewardData'] = RewardIncome::where('memberid', $result['data']['memberid'])->orderby('created_at', 'desc')->get();
        return view('member.income.reward-incomes')->with($result);
    }

    public function directIncomes()
    {
        $result['data'] = MemberDetail::where('memberid', session('MEMBER_ID'))->first();
        $result['directData'] = DirectIncome::where('memberid', $result['data']['memberid'])->orderby('created_at', 'desc')->get();
        return view('member.income.direct-incomes')->with($result);
    }

    public function monthlyRoiIncomes()
    {
        $result['data'] = MemberDetail::where('memberid', session('MEMBER_ID'))->first();
        $result['roiData'] = StakingIncome::where('memberid', $result['data']['memberid'])->orderby('created_at', 'desc')->get();
        return view('member.income.monthly-roi-incomes')->with($result);
    }
}
