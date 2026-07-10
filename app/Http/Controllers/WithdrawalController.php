<?php

namespace App\Http\Controllers;

use App\Models\MemberDetail;
use App\Models\WalletTransfer;
use App\Models\SetRate;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function mainWallets()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['rate'] = SetRate::find(1);
        return view('member.wallet.main-wallet')->with($result);
    }

    public function sendWithdrawalOtp()
    {
        $memberid = session('MEMBER_ID');
        $result = MemberDetail::where('memberid', $memberid)->first();
        $memberid = $result->memberid;
        $email = $result->email;
        $otp = rand(100000, 999999);
        session()->put('withdrawal_otp', $otp);

        if ($email != '') {
            $mailData = [
                'otp' => $otp,
                'memberid' => $memberid,
            ];
            $user['to'] = $email;
            // Mail::send('member.mails.withdrawal-mail', $mailData, function ($message) use ($user) {

            //     $message->to($user['to']);
            //     $message->subject('Lyra Arbitrage Member Withdrawal OTP');
            // });
        }

        session()->flash('OtpMsg', 'Your OTP has been sent to registered email');
        return redirect()->back();
    }

    public function initWithdrawal(Request $request, WithdrawalService $withdrawalService)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'txn_password' => 'required',
        ]);

        $memberid = $request->post('memberid');
        $amount = (float) $request->post('amount');
        $txnPass = $request->post('txn_password');

        $mem = MemberDetail::where('memberid', $memberid)->first();

        if (!Hash::check($txnPass, $mem->txn_password)) {
            session()->flash('failedMsg', 'Enter Transaction password do not match. Please check and try again');
            return redirect()->back();
        }

        try {
            $withdrawalService->requestWithdrawal($memberid, $amount);
        } catch (\Throwable $e) {
            session()->flash('failedMsg', $e->getMessage());
            return redirect()->back();
        }

        session()->flash('successMsg', 'Withdrawal request has been created successfully. Amount will be transfered into wallet after transaction validation.');
        return redirect()->back();
    }

    public function withdrlHistory()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['wdata'] = WithdrawalRequest::where('memberid', $memberid)->orderBy('created_at', 'desc')->get();
        return view('member.wallet.withdrawal-history')->with($result);
    }

    public function walletTransfer()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['transdata'] = WalletTransfer::where([['memberid', $memberid], ['walletType', '!=', 'P2P Fund Transfer']])->orderBy('created_at', 'desc')->get();
        return view('member.wallet.wallet-transfer')->with($result);
    }
}
