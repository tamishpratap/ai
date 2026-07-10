<?php

namespace App\Http\Controllers;

use App\Models\MemberDetail;
use App\Models\WalletTransfer;
use App\Models\Admin;
use App\Models\SetRate;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

    public function initWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required',
            'txn_password' => 'required',
        ]);

        $memberid = $request->post('memberid');
        $amount = $request->post('amount');
        $txnPass = $request->post('txn_password');

        // $service = $amount / 100 * 5; // 5% service charge
        // $netAmount = $amount - $service;
        $totalAmount = $amount;
        $date = date('Y-m-d H:i:s');
        $requestid = "RQ" . time();

        $mem = MemberDetail::where('memberid', $memberid)->first();
        $wallet = $mem->wallet;
        $tPassword = $mem['txn_password'];
        if ($mem['wallet_address'] == '' || $mem['wallet_address'] == null) {
            session()->flash('failedMsg', 'Your wallet address is not completed. Plase fill your wallet address before withdrawal');
            return redirect()->back();
        }

        $check = WithdrawalRequest::where([['memberid', $memberid], ['status', 'Pending']])->count();
        if ($check > 0) {
            session()->flash('failedMsg', 'Your Last Withdrawal Still Pending. Please Wait');
            return redirect()->back();
        }

        if ($amount < 10) {
            session()->flash('failedMsg', 'Invalid Amount Entered. Amount must be grater than 10$');
            return redirect()->back();
        }


        if ($amount > $wallet) {
            session()->flash('failedMsg', 'Invalid Amount Entered. Amount can not be greater than wallet balance');
            return redirect()->back();
        }

        if (!Hash::check($txnPass, $tPassword)) {
            session()->flash('failedMsg', 'Enter Transaction password do not match. Please check and try again');
            return redirect()->back();
        }


        $mem->wallet -= $totalAmount;
        $mem->save();

        $var = new WithdrawalRequest();
        $var->request_date = $date;
        $var->request_id = $requestid;
        $var->memberid = $memberid;
        $var->name = $mem->name;
        $var->type = 'Online';
        $var->wallet_address = $mem->wallet_address;
        $var->gross_amount = $totalAmount;
        $var->service_charge = 0;
        $var->net_amount = $totalAmount;
        $var->save();

        walletTransfer($memberid, $totalAmount, 'credit', $wallet, 'Withdrawal', ' $ ' . $totalAmount . ' have been withdrawl by Member from wallet. Payment Id-' . $var->request_id);

        $mailData = [
            'request_id' => $requestid,
            'email' => $mem->email,
            'memberid' => $memberid,
            'gross' => $var->gross_amount,
            'service' => $var->service_charge,
            'social' => $var->social_service,
            'support' => $var->support,
            'net' => $var->net_amount,
            'date' => date('Y-m-d H:i:s'),
            'name' => $mem->name,
            'wallet' => $mem->wallet_address,
            'type' => 'Wallet'
        ];

        // $user['to'] = 'arbitx007@gmail.com';
        // Mail::send('member.mails.withdrawal-request', $mailData, function ($message) use ($user) {

        //     $message->to($user['to']);
        //     $message->subject('Arbitx Member Withdrawal Request');
        // });

        session()->flash('successMsg', 'Withdrawal request has been created successfully. Amount will be transfered into wallet after transaction validation.');
        return redirect()->back();
    }

    public function withdrawalaccept(Request $request, $id)
    {
        $aid = session('ADMIN_ID');
        $result['adm_data'] = Admin::find($aid);
        $result['data'] = WithdrawalRequest::find($id);
        return view('admin.withdrawal-accept')->with($result);
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
