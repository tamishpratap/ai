<?php

namespace App\Http\Controllers;

use App\Models\MemberDetail;
use App\Models\StackingIncome;
use App\Models\StakingDetail;
use App\Models\SetRate;
use App\Models\PackageDetail;
use App\Models\StakingIncome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StakingController extends Controller
{
    public function staking()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        // $result['p_data'] = StakingDetail::all();
        return view('member.staking.staking', $result);
    }

    public function stakingDetails()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['sdata'] = StakingDetail::where('memberid', $result['data']['memberid'])->orderby('created_at', 'desc')->get();
        return view('member.staking.staking-details')->with($result);
    }

    public function stakingIncomes()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['sData'] = StakingIncome::where('memberid', $result['data']['memberid'])->orderby('created_at', 'desc')->get();
        return view('member.staking.staking-incomes')->with($result);
    }

    public function createStaking(Request $request)
    {
        $request->validate([
            'memberid' => 'required',
            'stak_package' => 'required',
            'txn_password' => 'required'
        ]);

        $memberid = session('MEMBER_ID');
        $txn_pas = $request->post('txn_password');
        $amount = $request->post('stak_package');

        $memUpdate = MemberDetail::where('memberid', $memberid)->first();
        $sponsorid = $memUpdate->sponsorid;
        $p2p_wallet = $memUpdate->p2p_wallet;
        $txn_password = $memUpdate->txn_password;
        $type = 'Staking';
        $rates = SetRate::find(1);

        $allowedPackages = [10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000, 25000];
        if (!in_array((int)$amount, $allowedPackages, true)) {
            session()->flash('failedMsg', 'Invalid package selected. Allowed packages: $10, $25, $50, $100, $250, $500, $1,000, $2,500, $5,000, $10,000, $25,000');
            return redirect()->back();
        }
        if (!Hash::check($txn_pas, $txn_password)) {
            session()->flash('failedMsg', 'Entered Transaction Password do not match');
            return redirect()->back();
        }
        if ($p2p_wallet < $amount) {
            session()->flash('failedMsg', 'There is no enough balance in your wallet to stake this amount');
            return redirect()->back();
        }

        $var = new StakingDetail();
        $var->memberid = $memberid;
        $var->invest_date = date('Y-m-d H:i:s');
        $var->invest_amount = $amount;
        $var->package = $amount;
        $var->status = 'Active';
        $var->rate = $rates->roi_rate;
        $var->save();

        $pack = new PackageDetail();
        $pack->txn_date = date('Y-m-d H:i:s');
        $order_id = "OD" . time();
        $pack->order_id = $order_id;
        $pack->payment_mode = 'Online';
        $pack->memberid = $memberid;
        $pack->package_value = $amount;
        $pack->package_type = $type;
        $pack->save();

        $p2p_wallet = $memUpdate->p2p_wallet;
        $memUpdate->self_biz += $amount;
        $memUpdate->team_biz += $amount;
        $memUpdate->p2p_wallet -= $amount;
        $memUpdate->package = $amount;

        if ($memUpdate->status == 'Temp') {
            $memUpdate->status = 'Active';
            $memUpdate->activated_at = date('Y-m-d H:i:s');
            updateDownline($sponsorid);
            updateUpline($sponsorid, $memberid);
        }
        $memUpdate->save();

        team_invest_update($sponsorid, $amount);
        walletTransfer($memberid, $amount, 'credit', $p2p_wallet, 'Investment Created', 'Investment creation amount has been deducted from fund wallet');

        session()->flash('successMsg', 'Investment has been created successfully.');
        return redirect()->back();
    }
    
    public function breakAmount($id)
    {
        $qry = StakingDetail::find($id);
        $memberid = $qry->memberid;
        if ($qry->status != 'Active') {
            session()->flash('failedMsg', 'This Investment is already Deactive');
            return redirect()->back();
        }
        $date = $qry->invest_date;
        $startdate = date('Y-m-d', strtotime($date.'+6 Months'));
        $invest_amount = $qry->invest_amount;
        if (date('Y-m-d') >= $startdate) {
            $service = $invest_amount * 10 / 100;
            $rate = 10;
        } else {
            $service = $invest_amount * 15 / 100;
            $rate = 15;
        }
        $net_amount = $invest_amount - $service;
        $qry->break_amount = $net_amount;
        $qry->rate = $rate;
        $qry->break_date = date('Y-m-d');
        $qry->deduction = $service;
        $qry->status = "Deactive";
        $qry->save();

        $member = MemberDetail::where('memberid', $memberid)->first();
        $principal = $member->principal_wallet;
        $member->principal_wallet += $net_amount;
        $member->save();

        walletTransfer($memberid, $net_amount, 'debit', $principal, 'principal withdrawal', 'principal withdrawal Amount Added into principal wallet.');

        session()->flash('successMsg', 'Investment has been Break successfully.');
        return redirect()->back();
    }

    public function claimROI(Request $request)
    {
        $request->validate([
            'memberid' => 'required'
        ]);

        $memberid = $request->post('memberid');
        $member = MemberDetail::where('memberid', $memberid)->first();
        if (!$member) {
            session()->flash('failedMsg', 'Member not found.');
            return redirect()->back();
        }
        
        if ($member->status != 'Active') {
            session()->flash('failedMsg', 'Please activate your account first');
            return redirect()->back();
        }
        
        
        $check = StakingIncome::where('memberid', $memberid)->whereDate('created_at', date('Y-m-d'))->first();
        if (!isset($check)) {
            session()->flash('failedMsg', 'Your trade is deactive.');
            return redirect()->back();
        }
        
        if (isset($check) && $check->status == 'Paid') {
            session()->flash('failedMsg', 'Trade already placed. Try again tomorrow.');
            return redirect()->back();
        }
        
        $totalROI = $check->amount;
        $wallet = $member->wallet;
        $member->wallet += $totalROI;
        $member->save();

        StakingIncome::where([['memberid', $memberid], ['status', 'Unclaimed']])->whereDate('created_at', date('Y-m-d'))->update(['status' => 'Paid']);
        walletTransfer($memberid, $totalROI, 'debit', $wallet, 'Daily Profit', 'Daily Profit amount has been added to your P2P wallet.');

        session()->flash('successMsg', 'Trade placed successfully');
        return redirect()->back();
    }
}
