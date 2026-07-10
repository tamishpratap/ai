<?php

namespace App\Http\Controllers;

use App\Models\MemberDetail;
use App\Models\Package;
use App\Models\StakingDetail;
use App\Models\StakingIncome;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StakingController extends Controller
{
    public function staking()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['packages'] = Package::where('is_active', true)
            ->where('amount', '>', $result['data']->package)
            ->orderBy('sort_order')
            ->get();
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

    public function createStaking(Request $request, PackageService $packageService)
    {
        $request->validate([
            'memberid' => 'required',
            'stak_package' => 'required|numeric',
            'txn_password' => 'required'
        ]);

        $memberid = session('MEMBER_ID');
        $amount = (float) $request->post('stak_package');
        $member = MemberDetail::where('memberid', $memberid)->first();

        if (!Hash::check($request->post('txn_password'), $member->txn_password)) {
            session()->flash('failedMsg', 'Entered Transaction Password do not match');
            return redirect()->back();
        }

        try {
            if ((float) $member->package > 0) {
                $packageService->upgradePackage($memberid, $amount);
            } else {
                $packageService->activatePackage($memberid, $amount);
            }
        } catch (\Throwable $e) {
            session()->flash('failedMsg', $e->getMessage());
            return redirect()->back();
        }

        session()->flash('successMsg', 'Investment has been created successfully.');
        return redirect()->back();
    }

    public function createStakingTopup(Request $request, PackageService $packageService)
    {
        $request->validate([
            'stak_package' => 'required|numeric',
            'txn_password' => 'required'
        ]);

        $memberid = session('MEMBER_ID');
        $amount = (float) $request->post('stak_package');
        $member = MemberDetail::where('memberid', $memberid)->first();

        if (!Hash::check($request->post('txn_password'), $member->txn_password)) {
            session()->flash('failedMsg', 'Entered Transaction Password do not match');
            return redirect()->back();
        }

        try {
            $packageService->upgradePackage($memberid, $amount);
        } catch (\Throwable $e) {
            session()->flash('failedMsg', $e->getMessage());
            return redirect()->back();
        }

        session()->flash('successMsg', 'Package has been upgraded successfully.');
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
