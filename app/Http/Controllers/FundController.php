<?php

namespace App\Http\Controllers;

use App\Models\PaymentDetail;
use App\Models\MemberDetail;
use App\Models\FundRequest;
use App\Models\SetRate;
use App\Models\WalletTransfer;
use App\Services\WalletService;
use Illuminate\Http\Request;
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

    public function USDTFundReq(Request $request)
    {
        $request->validate([
            'memberid' => 'required',
            'amount' => 'required|numeric',
            'txnid' => 'required',
            'image' => 'required'
        ]);

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

        p2pwalletTransfer($memberid, $amount, 'debit', $p2pwallet, 'P2P Fund Transfer', 'Fund requested transfered into user fund wallet');
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
}
