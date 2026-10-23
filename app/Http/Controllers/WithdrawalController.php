<?php

namespace App\Http\Controllers;

use App\Models\MemberDetail;
use App\Models\WalletTransfer;
use App\Models\SetRate;
use App\Models\Admin;
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
    public function withdrawalaccept(Request $request, $id)
    {
        $aid = session('ADMIN_ID');
        $result['adm_data'] = Admin::find($aid);
        $result['udata'] = WithdrawalRequest::find($id);
        return view('admin.withdrawal-accept')->with($result);
    }
    public function sendWdlRequest(Request $request)
    {
        $memberid = $request->post('memberid');
        $wallet = $request->post('wallet');
        $net_amount = $request->post('net_amount');
        $wid = $request->post('wid');
        $jwt = getJWttoken();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.nowpayments.io/v1/payout',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "ipn_callback_url": "https://nowpayments.io",
            "withdrawals": [
                {
                    "address": "' . $wallet . '",
                    "currency": "USDTBSC",
                    "amount": ' . $net_amount . ',
                    "ipn_callback_url": "https://nowpayments.io"
                }
            ]
        }',
            CURLOPT_HTTPHEADER => array(
                'x-api-key: ' . config('detailsApp.nowpayments_api_key'),
                'Content-Type: application/json',
                'Authorization: Bearer ' . $jwt . ''
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, true);
        //print_r($data);
        if (isset($data['id'])) {
            $var = WithdrawalRequest::find($wid);
            $var->batch_withdrawal_id = $data['withdrawals'][0]['batch_withdrawal_id'];
            $var->status = $data['withdrawals'][0]['status'];
            $var->save();

            session()->flash('successMsg', 'Withdrawal request has been created successfully!. Status is ' . $var->status . '');
            return redirect('admin/new-withdrawal-request');
        } else {
            session()->flash('failedMsg', $data['message']);
            return redirect('admin/new-withdrawal-request');
        }
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
        if ($amount < 10) {
            session()->flash('failedMsg', 'Minimum amount for withdrawal is $10');
            return redirect()->back();
        }

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
