<?php

namespace App\Http\Controllers;

use App\Models\PaymentDetail;
use App\Models\MemberDetail;
use App\Models\FundRequest;
use App\Models\SetRate;
use App\Models\WalletTransfer;
use Illuminate\Http\Request;

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
    
    public function fundTrans(Request $request)
    {
        $request->validate([
            'amount' => 'required',
            'memberid' => 'required',
            'txn_password'=>'required'
        ]);
        $memberid = strtoupper($request->post('memberid'));
        $val_id = MemberDetail::where('memberid', $memberid)->first();
        if ($val_id && $val_id->status != 'Blocked') {

            $sendData = MemberDetail::where('memberid',session('MEMBER_ID'))->first();
            $senderid = $sendData->memberid;
            $var = MemberDetail::where('memberid', $senderid)->first();
            $txn_password = $request->post('txn_password');
            $amount = $request->post('amount');
            if ($amount < 0) {
                session()->flash('failedMsg', 'Negative value is not allowed');
                return redirect()->back();
            }
            
            if ($val_id->status == 'Deactive') {
                session()->flash('failedMsg', 'Entered memberid is deactive. Amount cannot be transferred to deactive account');
                return redirect()->back();
            }
            
            if ($var->status == 'Deactive') {
                session()->flash('failedMsg', 'Your account status is deactive. Please contact system admin');
                return redirect()->back();
            }
            
            if ($amount > $var['p2p_wallet']) {
                session()->flash('failedMsg', 'You do not have enough balance to transfer this amount ');
                return redirect()->back();
            }
            if ($memberid == $senderid) {
                session()->flash('failedMsg', 'You can not transfer money to your own account');
                return redirect()->back();
            } else {

                //sending Money
                $qry = MemberDetail::where('memberid', $memberid)->first();
                $p2p_wallet = $qry->p2p_wallet;
                $qry->p2p_wallet += $amount;
                $qry->save();
                p2pwalletTransfer($memberid, $amount, 'debit', $p2p_wallet, 'P2P Fund Transfer', '$ ' . $amount . ' has been transferred to your p2p wallet by ' . $senderid . '');

                $query = MemberDetail::where('memberid', $senderid)->first();
                $senderwallet = $query->p2p_wallet;
                $query->p2p_wallet -= $amount;
                $query->save();

                p2pwalletTransfer($senderid, $amount, 'credit', $senderwallet, 'P2P Fund Transfer', '$ ' . $amount . ' has been transferred by you to ' . $memberid . ' p2p wallet');

                // $trans = new P2pFundTransfers();
                // $trans->senderid = $senderid;
                // $trans->amount = $amount;
                // $trans->deduction = 0;
                // $trans->net_amount = $amount;
                // $trans->receiverid = $memberid;
                // $trans->save();

                session()->flash('successMsg', 'Amount has been transferred successfully');
                return redirect()->back();
            }
        } else {
            session()->flash('failedMsg', 'Entered Memberid is either blocked or unavailable');
            return redirect()->back();
        }
    }
}
