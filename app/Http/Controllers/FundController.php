<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use App\Models\ImportFund;
use App\Models\MemberDetail;
use App\Models\NowpaymentsPayment;
use App\Models\PaymentData;
use App\Models\PaymentDetail;
use App\Models\WalletTransfer;
use App\Models\SetRate;
use Illuminate\Support\Str;
use App\Services\WalletService;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class FundController extends Controller
{
    public function importfund()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['rate'] = SetRate::find(1);
        $result['usdt_data'] = PaymentDetail::where([['address', '!=', NULL], ['status', 'Live']])->first();
        return view('member.fund.import-fund')->with($result);
    }
    public function instantFunds(Request $request)
    {
        $request->validate([
            'inr_amount' => 'required',
            'amount' => 'required|min:0',
            'security_pin' => 'required'
        ]);

        $memberid = session('MEMBER_ID');
        $amount = $request->post('amount');
        $security_pin = $request->post('security_pin');
        $orderid = Str::random(20) . rand(1000, 9999);

        $result = MemberDetail::where('memberid', $memberid)->first();
        $Hashpass = $result['txn_password'];
        if (!Hash::check($security_pin, $Hashpass)) {
            session()->flash('failedMsg', 'Please enter valid security pin .');
            return redirect()->back();
        }

        if ($amount < 100) {
            session()->flash('failedMsg', ' Minimum amount for import fund is $100');
            return redirect()->back();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.nowpayments.io/v1/invoice");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $post = array(
            "price_amount" => $amount,
            "price_currency" => "usd",
            "order_id" => $orderid,
            "order_description" => "Deposit Request From " . $memberid,
            "ipn_callback_url" => "https://nowpayments.io",
            "success_url" => config('detailsApp.url') . "member/payment-success/" . $orderid,
            "cancel_url" => config('detailsApp.url') . "member/payment-cancel/" . $orderid,
            "partially_paid_url" => config('detailsApp.url') . "member/payment-success/" . $orderid
        );

        $post = json_encode($post);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "x-api-key: " . env('nowpayments_api_key');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result, true);
        print_R($response);
        $var = new NowpaymentsPayment();
        $var->memberid = $memberid;
        $var->status = 'WAITING';
        $var->payment_id = $response['id'];
        $var->order_id = $orderid;
        $var->amount = $amount;
        $var->save();

        return redirect()->to($response['invoice_url']);
    }

    public function paySuccess($orderid)
    {
        $orderid;
        $jwt = getJWttoken();
        $datefirst = date('Y-m-d', strtotime('-1day'));
        $datelast = date('Y-m-d', strtotime('+1day'));
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.nowpayments.io/v1/payment/?limit=20&page=0&sortBy=created_at&orderBy=desc&dateFrom=' . $datefirst . '&dateTo=' . $datelast . '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'x-api-key: ' . env('nowpayments_api_key'),
                'Authorization: Bearer ' . $jwt . ''
            ),
        ));

        $response = curl_exec($curl);
        $data = json_decode($response, true);
        curl_close($curl);
        //print_r($data['data']);
        foreach ($data['data'] as $value) {
            echo $value['order_id'] . '- ' . $value['created_at'];
            echo "<br>";
            if ($orderid == $value['order_id']) {
                $value['order_id'];
                $qry = new PaymentData();
                $qry->payment_id = $value['payment_id'];
                $qry->payment_status = $value['payment_status'];
                $qry->price_amount = $value['price_amount'];
                $qry->pay_amount = $value['pay_amount'];
                $qry->actually_paid = $value['actually_paid'];
                $qry->order_id = $value['order_id'];
                $qry->purchase_id = $value['purchase_id'];
                $qry->outcome_amount = $value['outcome_amount'];
                $qry->save();
            }
        }
        $check = PaymentData::where('order_id', $orderid)->first();
        $paidAmount = $check['actually_paid'];
        $paid_amount = $paidAmount;
        $status = $check['payment_status'];
        $payment_id = $check['payment_id'];
        $var = NowpaymentsPayment::where([['order_id', $orderid], ['status', 'WAITING']])->first();
        if (!$var) {
            session()->flash('failedMsg', 'Invalid OrderId Passed');
            return redirect('member/fund/add-fund');
        }
        $var->status = strtoupper($status);
        $var->payment_id = $payment_id;
        $var->save();

        $memberid = $var->memberid;

        $qry = MemberDetail::where('memberid', $memberid)->first();
        $wallet = $qry->p2p_wallet;
        $qry->p2p_wallet += $paid_amount;
        $qry->save();

        walletTransfer($memberid, $paid_amount, 'debit', $wallet, 'Import Funds', 'Impoter fund added into fund wallet');

        $varimp = new ImportFund();
        $varimp->memberid = $memberid;
        $varimp->amount = $paid_amount;
        $varimp->orderid = $orderid;
        $varimp->txnid = $payment_id;
        $varimp->status = 'Approved';
        $varimp->mode = 'Online';
        $varimp->added_by = 'User';
        $varimp->save();

        session()->flash('successMsg', 'Amount has been imported into wallet successfully');
        return redirect('member/fund/add-fund');
    }

    public function payCancel($memberid)
    {
        $var = NowpaymentsPayment::where('memberid', $memberid)->orderBy('created_at', 'desc')->first();
        $var->status = 'CANCELLED';
        $var->save();

        session()->flash('failedMsg', 'Import Funds request has been Cancelled. Please try again');
        return redirect('member/fund/add-fund');
    }
    public function USDTFundReq(Request $request)
    {
        $request->validate([
            'memberid' => 'required',
            'amount' => 'required|numeric',
            'txnid' => 'required',
            'image' => 'required'
        ]);

        if (Setting::get('deposit_status', 'enabled') !== 'enabled') {
            session()->flash('failedMsg', 'Deposits are temporarily disabled. Please try again later.');
            return redirect()->back();
        }

        if ($request->post('amount') < 0) {
            session()->flash('failedMsg', 'Invalid amount input. Please change amount value');
            return redirect()->back();
        }

        if ($request->post('amount') < 1) {
            session()->flash('failedMsg', 'Amount must be greater than or equal to $1');
            return redirect()->back();
        }

        if (checkTxnid($request->post('txnid')) == 1) {
            session()->flash('failedMsg', 'This transaction id has already been used. Please change transaction id');
            return redirect()->back();
        }

        $var = new FundRequest();
        $var->date = date('Y-m-d');
        $var->memberid = $request->post('memberid');
        $var->amount = $request->post('amount');
        $var->type = 'USDT';
        $var->tran_id = $request->post('txnid');
        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension();
        if ($ext != 'png' && $ext != 'jpg' && $ext != 'jpeg') {
            session()->flash('failedMsg', 'Allowed image type is jpg, jpeg, png. Please change image type');
            return redirect()->back();
        }
        $filename = 'txn' . time() . '.' . $ext;
        $file->move(public_path('uploads/qr-code'), $filename);
        $var->image = $filename;
        $var->status = 'Requested';
        $var->save();

        session()->flash('successMsg', 'Your request has been created successfully.');
        return redirect()->back();
    }

    public function acceptReq(Request $request, $id)
    {
        $var = FundRequest::find($id);
        $memberid = $var->memberid;
        $amount = $var->amount;
        $var->status = 'Accepted';
        $var->save();


        $qry = MemberDetail::where('memberid', $memberid)->first();
        $p2pwallet = $qry->p2p_wallet;
        $qry->p2p_wallet += $amount;
        $qry->save();

        p2pwalletTransfer($memberid, $amount, 'debit', $p2pwallet, 'P2P Fund Transfer', 'Fund requested transfered into user fund wallet', $var->tran_id);

        // Mirrored here so admin's existing add-funds reporting (ImportFund-backed) stays
        // complete now that the member-facing self-credit path (FundController::addFund,
        // removed - it let members credit any wallet with an unvalidated amount) is gone.
        $importFund = new ImportFund();
        $importFund->memberid = $memberid;
        $importFund->orderid = 'FR' . $var->id;
        $importFund->txnid = $var->tran_id;
        $importFund->amount = $amount;
        $importFund->type = 'Add';
        $importFund->mode = 'Manual';
        $importFund->status = 'Approved';
        $importFund->added_by = 'Admin';
        $importFund->wallet_type = 'P2P Wallet';
        $importFund->save();

        session()->flash('successMsg', 'Fund request has been accepted successfully');
        return redirect()->back();
    }

    public function rejectReq(Request $request, $id)
    {
        $var = FundRequest::find($id);
        $var->status = 'Cancelled';
        $var->save();

        session()->flash('successMsg', 'Fund request has been rejected successfully');
        return redirect()->back();
    }

    public function impfundhis(Request $request)
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['addfundData'] = FundRequest::where('memberid', $memberid)->orderBy('created_at', 'desc')->get();
        return view('member.fund.import-fund-history')->with($result);
    }
    
    public function p2pwallet(Request $request)
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $memberid = $result['data']['memberid'];
        $result['passdata'] = WalletTransfer::where([['memberid', $result['data']['memberid']], ['walletType', 'P2P Fund Transfer']])->orderBy('created_at', 'desc')->get();
        return view('member.p2p.p2p-wallet')->with($result);
    }

    public function p2pHistory(Request $request)
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid',  $memberid)->first();
        $memberid = $result['data']['memberid'];
        $result['passdata'] = WalletTransfer::where([['memberid', $result['data']['memberid']], ['walletType', 'P2P Fund Transfer']])->orderBy('created_at', 'desc')->get();
        return view('member.p2p.p2p-fund-passbook')->with($result);
    }
    
    public function fundTrans(Request $request, WalletService $walletService)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'memberid' => 'required',
            'txn_password' => 'required',
        ]);

        $receiverMemberid = strtoupper($request->post('memberid'));
        $senderMemberid = session('MEMBER_ID');
        $amount = (float) $request->post('amount');

        $sender = MemberDetail::where('memberid', $senderMemberid)->first();

        if (!Hash::check($request->post('txn_password'), $sender->txn_password)) {
            session()->flash('failedMsg', 'Entered Transaction Password do not match');
            return redirect()->back();
        }

        try {
            $walletService->transferFunds($senderMemberid, $receiverMemberid, $amount);
        } catch (\Throwable $e) {
            session()->flash('failedMsg', $e->getMessage());
            return redirect()->back();
        }

        session()->flash('successMsg', 'Amount has been transferred successfully');
        return redirect()->back();
    }
    
    
      public function walletToP2P(Request $request)
    {
        $memberid = session('MEMBER_ID');

        $result['data'] = MemberDetail::where('memberid', $memberid)->first();

        if (!$memberid || !$result['data']) {
            session()->flash('failedMsg', 'Member session is invalid. Please login again.');
            return redirect('/member');
        }

        $result['passdata'] = WalletTransfer::where([
            ['memberid', $memberid],
            ['walletType', 'P2P Fund Transfer']
        ])->orderBy('created_at', 'desc')->get();

        return view('member/wallet/wallet-to-p2p')->with($result);
    }

    public function walletP2pTransfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'memberid' => 'required',
            'pin' => 'required'
        ]);

        $senderid = session('MEMBER_ID');
        $memberid = strtoupper($request->post('memberid', $senderid));
        $var = MemberDetail::where('memberid', $senderid)->first();
        if (!$var) {
            session()->flash('failedMsg', 'Member session is invalid. Please login again.');
            return redirect()->back();
        }

        $balance = $var->wallet;
        $txn_password = $request->post('pin');
        $amount = $request->post('amount');
        
        $deduction = $amount / 100 * 5; // 5% service charge
        $netAmount = $amount - $deduction;

        if ($amount > $balance) {
            session()->flash('failedMsg', 'You do not have enough balance to transfer this amount ');
            return redirect()->back();
        }

        $txnpassword = $var->txn_password;
        if (Hash::check($txn_password, $txnpassword)) {
            //sending Money
            $qry = MemberDetail::where('memberid', $memberid)->first();
            if ($qry) {

                $p2p_wallet = $qry->p2p_wallet;
                $qry->p2p_wallet += $netAmount;
                $qry->save();

                p2pwalletTransfer($memberid, $netAmount, 'debit', $p2p_wallet, 'P2P Fund Transfer', '$ ' . $amount . ' has been transferred to your wallet by ' . $senderid . '');
                p2pwalletTransfer($memberid, $deduction, 'credit', $p2p_wallet, 'P2P Fund Deduction', '$ ' . $deduction . ' has been deducted for transfer charges');

                $query = MemberDetail::where('memberid', $senderid)->first();
                $senderwallet = $query->wallet;
                $query->wallet -= $amount;
                $query->save();

                walletTransfer($senderid, $amount, 'credit', $senderwallet, 'Wallet to p2p Transfer', '$ ' . $amount . ' has been transferred by you to ' . $memberid . '  wallet');

                session()->flash('successMsg', 'Amount has been transferred successfully');
                return redirect()->back();
            } else {
                session()->flash('failedMsg', 'Entered Memberid is not unavailable');
                return redirect()->back();
            }
        } else {
            session()->flash('failedMsg', 'Entered Transaction Password donot match');
            return redirect()->back();
        }
    }
}
