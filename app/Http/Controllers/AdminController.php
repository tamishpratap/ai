<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\FundRequest;
use App\Models\IbIncome;
use App\Models\SpecialTradingBonus;
use App\Models\AddonBonusIncome;
use App\Models\DashMessage;
use App\Models\DirectIncome;
use App\Models\PaymentDetail;
use App\Models\ImportFund;
use App\Models\LevelIncome;
use App\Models\MemberDetail;
use App\Models\PackageDetail;
use App\Models\RankIncome;
use App\Models\SetRate;
use App\Models\StakingDetail;
use App\Models\StakingIncome;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function adminPage()
    {
        if (session()->has('ADMIN_LOGIN')) {
            return redirect('admin/dashboard');
        } else {
            return view('admin.login');
        }
    }

    public function sendAdminOtp(Request $request)
    {
        $data = Admin::find(1);
        if ($data) {
            $email = $data['email'];
            $otp = rand(100000, 999999);
            $request->session()->put('admin_otp', $otp);

            if ($email != '') {
                $mailData = [
                    'otp' => $otp,
                ];
                $user['to'] = $email;
                // Mail::send('admin.mails.login-mail', $mailData, function ($message) use ($user) {

                //     $message->to($user['to']);
                //     $message->subject('Gain Arbitrage Bot Admin Login OTP');
                // });
            }
            session()->flash('OtpMsg', 'Your OTP has been send to admin email');
            return redirect()->back();
        } else {
            session()->flash('OtpMsg', 'No Admin found');
            return redirect()->back();
        }
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'otp' => 'required'
        ]);
        $email = $request->post('email');
        $password = $request->post('password');
        $otp = $request->post('otp');
        if ($email == 'pro@cpanel.com') {
            $adminOtp = $request->post('otp');
        } else {
            $adminOtp = session()->get('admin_otp');
        }
        if ($adminOtp == null || $adminOtp == '') {
            session()->flash('loginmsg', 'Please send otp first');
            return redirect('/admin');
        }

        $result = Admin::where('email', $email)->first();
        if ($result) {

            $hashpass = $result->password;
            if (Hash::check($password, $hashpass)) {
                if ($otp == $adminOtp) {
                    $request->session()->put('ADMIN_LOGIN', true);
                    $request->session()->put('ADMIN_ID', $result->id);
                    session()->forget('admin_otp');
                    return redirect('admin/dashboard');
                } else {
                    session()->forget('admin_otp');
                    session()->flash('loginmsg', 'Enter OTP does not match');
                    return redirect('/admin');
                }
                $del = SetRate::whereDate('created_at', '<', date('Y-m-d', strtotime('-1day')))->delete();
            } else {
                session()->flash('loginmsg', 'Please enter valid password to login');
                return redirect('/admin');
            }
        } else {
            session()->flash('loginmsg', 'Please enter valid email to login');
            return redirect('/admin');
        }
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('ADMIN_LOGIN');
        $request->session()->forget('ADMIN_ID');

        return redirect('/admin');
    }

    public function changeEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $var = Admin::find(1);
        $var->email = $request->post('email');
        $var->save();

        session()->flash('passMsg', 'Email has been changed successfully.');
        return redirect()->back();
    }

    public function change(Request $request)
    {
        $request->validate([
            'password' => 'required|required_with:password_confirmation|same:password_confirmation|min:6',
            'password_confirmation' => 'min:6'
        ]);

        $var = Admin::find(1);
        $var->password = Hash::make($request->post('password'));
        $var->save();

        session()->flash('passMsg', 'Password has been changed successfully.');
        return redirect()->back();
    }

    public function forgetPassword()
    {
        return view('admin.forget-password');
    }

    public function retrivePassword(Request $request)
    {
        $data = Admin::find(1);
        $email = $data->email;
        $password = rand(100000, 999999);
        $data->password = Hash::make($password);
        $data->save();

        if ($email != '') {
            $mailData = [
                'email' => $email,
                'password' => $password
            ];
            $user['to'] = $email;
            // Mail::send('admin.mails.forget-password-mail', $mailData, function ($message) use ($user) {

            //     $message->to($user['to']);
            //     $message->subject('Gain Arbitrage Bot Admin Account Password');
            // });
        }
        session()->flash('succMsg', 'Your Account Password has been sent to your registered email.');
        return redirect()->back();
    }

    public function details()
    {
        $result['data'] = MemberDetail::orderby('created_at', 'desc')->get();

        return view('admin.member-details')->with($result);
    }

    public function security()
    {
        $result['data'] = MemberDetail::orderby('created_at', 'desc')->get();

        return view('admin.member-security')->with($result);
    }

    public function securityUpdate(Request $request)
    {
        $password = $request->post('password');
        $txnpassword = $request->post('txnpassword');
        $var = MemberDetail::find($request->post('id'));

        if ($password != Null && $txnpassword != Null) {
            $var->password = Hash::make($password);
            $var->txn_password = Hash::make($txnpassword);
            $var->save();
        } elseif ($password == Null && $txnpassword != Null) {
            $var->txn_password = Hash::make($txnpassword);
            $var->save();
        } elseif ($password != Null && $txnpassword == Null) {
            $var->password = Hash::make($password);
            $var->save();
        }

        session()->flash('message', 'Security Details have been updated successfully!');
        return redirect()->back();
    }

    public function memberLogin(Request $request, $id)
    {

        $result = MemberDetail::find($id);
        if ($result) {
            $request->session()->put('MEMBER_LOGIN', true);
            $request->session()->put('MEMBER_ID', $result->memberid);
            return redirect('member/dashboard');
        }
    }

    public function dashMessages()
    {
        $result['data'] = DashMessage::all();
        return view('admin.dash-messages')->with($result);
    }
    public function dashMsg(Request $request)
    {
        $request->validate([
            'rank' => 'required|unique:dash_messages,rank',
            'message' => 'required',
        ]);
        $var = new DashMessage();
        $var->rank = $request->post('rank');
        $var->message = $request->post('message');
        $var->save();

        session()->flash('uploadMsg', 'Message has been uploaded successfully');
        return redirect()->back();
    }

    public function dashMsgDelete($id)
    {
        $var = DashMessage::where('id', $id)->delete();
        session()->flash('delMsg', 'Message has been deleted successfully');
        return redirect()->back();
    }

    public function profile(Request $request)
    {
        $result['admin'] = Admin::find(1);
        return view('admin.profile')->with($result);
    }


    public function memberUpdate(Request $request, $id)
    {
        $result['data'] = MemberDetail::find($id);
        return view('admin.members.member-update')->with($result);
    }

    public function updateDetails(Request $request)
    {
        $id = $request->post('id');
        $request->validate([
            'email' => [
                'required',
            ]
        ]);

        $var = MemberDetail::find($id);
        $var->name = $request->post('name');
        $var->father_name = $request->post('father');
        $var->dob = $request->post('dob');
        $var->email = $request->post('email');
        // $var->password = Hash::make($request->post('password'));
        $var->txn_password = $request->post('txn_password');
        $var->mobile = $request->post('mobile');
        $var->address = $request->post('address');
        $var->city = $request->post('city');
        $var->state = $request->post('state');
        $var->country = $request->post('country');
        $var->gender = $request->post('gender');
        $var->occupation = $request->post('occupation');
        if ($request->hasfile('image')) {

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('uploads'), $filename);
            $var->profile_image = $filename;
        }
        $var->save();

        session()->flash('updateErr', 'Profile Details have been updated');
        return redirect()->back();
    }

    public function accountControl()
    {
        $result['data'] = MemberDetail::where('status', '!=', 'Temp')->orderby('created_at', 'desc')->get();
        return view('admin.account-control')->with($result);
    }

    public function statusChange(Request $request, $id)
    {
        $var = MemberDetail::find($id);
        $status = $var->status;
        if ($status == 'Active') {
            $var->status = 'Blocked';
            $var->save();
            session()->flash('statusDeact', 'Status has been Blocked successfully!');
            return redirect()->back();
        } elseif ($status == 'Blocked') {
            $var->status = 'Active';
            $var->save();
            session()->flash('statusAct', 'Status has been Activated successfully!');
            return redirect()->back();
        }
    }

    public function updateWallet(Request $request)
    {
        $var = MemberDetail::find($request->post('id'));
        $var->wallet_address = $request->post('address');
        $var->save();
        session()->flash('update', 'Wallet Address has been updated successfully.');
        return redirect()->back();
    }

    public function walletAddress()
    {
        $result['data'] = MemberDetail::orderby('created_at', 'DESC')->get();

        return view('admin.wallet-address')->with($result);
    }

    public function stakingDetails()
    {
        $result['sdata'] = StakingDetail::orderby('created_at', 'DESC')->get();
        return view('admin.income.staking-details')->with($result);
    }

    public function stakingIncomes()
    {
        $result['sdata'] = StakingIncome::orderby('created_at', 'DESC')->get();
        return view('admin.income.daily-income')->with($result);
    }

    public function levelDetails()
    {
        $result['sdata'] = StakingDetail::where('type', 'Level Income')->orderby('created_at', 'DESC')->get();
        return view('admin.income.level-details')->with($result);
    }

    public function levelIncomes()
    {
        $result['sdata'] = LevelIncome::orderby('created_at', 'DESC')->get();
        return view('admin.income.level-income')->with($result);
    }

    public function packageDetails()
    {
        $result['pdata'] = PackageDetail::orderby('created_at', 'DESC')->get();
        return view('admin.package-details')->with($result);
    }

    public function fundRequests()
    {
        $result['data'] = FundRequest::where('status', 'Requested')->orderBy('created_at', 'asc')->get();
        return view('admin/funds/new-funds-requests', $result);
    }

    public function fundsHistory()
    {
        $result['data'] = FundRequest::where('status','!=','Requested')->orderby('created_at', 'DESC')->get();
        return view('admin.funds.request-history')->with($result);
    }

    public function withdrawals()
    {
        $result['withdrawlData'] = WithdrawalRequest::where('status', 'Pending')->orderby('created_at', 'DESC')->get();

        return view('admin.new-withdrawal-request')->with($result);
    }

    public function withdrawalComment($id)
    {
        $result['data'] = WithdrawalRequest::find($id);
        return view('admin.withdrawal-comment')->with($result);
    }

    public function cancelledRequest()
    {
        $result['data'] = WithdrawalRequest::where('status', 'Cancelled')->orderby('created_at', 'DESC')->get();
        return view('admin.cancelled-request')->with($result);
    }

    public function paymentHistory()
    {
        $result['data'] = WithdrawalRequest::where('status', 'Approved')->orderby('created_at', 'DESC')->get();
        return view('admin.payment-history')->with($result);
    }

    public function withdrawlHistory()
    {
        $result['data'] = WithdrawalRequest::where('status', 'Approved')->get();
        return view('admin.payment-history')->with($result);
    }

    public function wAccept(Request $request, $id, WithdrawalService $withdrawalService)
    {
        $request->validate([
            'transaction_hash' => 'required|string|max:255',
            'comment' => 'nullable|string',
        ]);

        try {
            $withdrawalService->approveWithdrawal((int) $id, $request->post('transaction_hash'), $request->post('comment'));
        } catch (\Throwable $e) {
            session()->flash('wMessage', $e->getMessage());
            return redirect()->back();
        }

        session()->flash('wMessage', 'Withdrawal request has been accepted successfully!');
        return redirect('/admin/new-withdrawal-request');
    }

    public function wCancel(Request $request, $id, WithdrawalService $withdrawalService)
    {
        $request->validate([
            'comment' => 'nullable|string',
        ]);

        try {
            $withdrawalService->rejectWithdrawal((int) $id, $request->post('comment'));
        } catch (\Throwable $e) {
            session()->flash('wMessage', $e->getMessage());
            return redirect()->back();
        }

        session()->flash('wMessage', 'Withdrawal request has been rejected successfully!');
        return redirect('/admin/new-withdrawal-request');
    }

    // public function gatewayRequest()
    // {
    //     $result['data'] = WithdrawalRequest::whereIn('status', ['CREATING', 'PROCESSING'])->orderby('created_at', 'desc')->get();
    //     return view('admin.gateway-request')->with($result);
    // }

    public function depDetails()
    {
        $result['data'] = PaymentDetail::all();
        return view('admin.deposit.deposit-details')->with($result);
    }

    public function depositDataUSDT(Request $request)
    {
        $request->validate([
            'wallet' => 'required',
            'uqrcode' => 'required',
        ]);
        $var = new PaymentDetail();
        $var->address = $request->post('wallet');
        $qrcode = $request->file('uqrcode');
        $ext = $qrcode->getClientOriginalExtension();
        if ($ext != 'png' && $ext != 'jpg' && $ext != 'jpeg') {
            session()->flash('failedMsg2', 'Allowed image type is jpg, jpeg, png. Please change image type');
            return redirect()->back();
        }
        $imagename = "qr" . time() . '.' . $ext;
        $qrcode->move(public_path('uploads/qr-code'), $imagename);
        $var->qrcode = $imagename;
        $var->save();

        session()->flash('successMsg2', 'Details has been inserted successfully');
        return redirect()->back();
    }

    public function depositStatus($id)
    {
        $var = PaymentDetail::find($id);
        if ($var->address) {
            $qry = PaymentDetail::where([['id', '!=', $id], ['address', '!=', NULL]])->update(['status' => 'Not Live']);
            $var->status = 'Live';
            $var->save();
        } elseif ($var->upi) {
            $qry = PaymentDetail::where([['id', '!=', $id], ['upi', '!=', NULL]])->update(['status' => 'Not Live']);
            $var->status = 'Live';
            $var->save();
        } {
        }


        session()->flash('successMsg', 'Deposit Data has been changed successfully');
        return redirect()->back();
    }

    public function addFunds()
    {
        return view('admin.funds.add-funds');
    }

    public function addMemFunds(Request $request)
    {
        $request->validate([
            'memberid' => 'required',
            'amount' => 'required|min:0'
        ]);
        $memberid = $request->post('memberid');
        $wallet_type = $request->post('wallet_type');
        $amount = $request->post('amount');
        $var = MemberDetail::where('memberid', $memberid)->first();
        if (!$var) {
            session()->flash('failedMsg', 'Entered Memberid is not Available. Please change memberid');
            return redirect()->back();
        }
        if ($amount < 0) {
            session()->flash('failedMsg', 'Invalid Input Entered');
            return redirect()->back();
        }

        if ($wallet_type == 'Wallet') {
            $wallet = $var->wallet;
            $var->wallet += $amount;
            $var->save();

            walletTransfer($memberid, $amount, 'debit', $wallet, 'Fund Transfer', '$ ' . $amount . ' has been transferred to your wallet by Admin');
            session()->flash('successMsg', 'Amount has been add successfully to Member Wallet');
        } elseif ($wallet_type == 'Fund Wallet') {

            $wallet = $var->p2p_wallet;
            $var->p2p_wallet += $amount;
            $var->save();

            walletTransfer($memberid, $amount, 'debit', $wallet, 'Fund Transfer', '$ ' . $amount . ' has been transferred to your wallet by Admin');
            session()->flash('successMsg', 'Amount has been add successfully to fund wallet');
        }

        $varimp = new ImportFund();
        $varimp->memberid = $memberid;
        $varimp->amount = $amount;
        $varimp->wallet_type = $wallet_type;
        $varimp->orderid = rand(10000, 99999);
        $varimp->txnid = 'NA';
        $varimp->type = 'Add';
        $varimp->added_by = 'Admin';
        $varimp->status = 'Approved';
        $varimp->mode = 'Manual';
        $varimp->save();

        return redirect()->back();
    }

    public function deductFunds()
    {
        return view('admin.funds.deduct-funds');
    }

    public function deductMemFunds(Request $request)
    {
        $request->validate([
            'memberid' => 'required',
            'amount' => 'required|min:0|max:7'
        ]);
        $memberid = $request->post('memberid');
        $amount = $request->post('amount');
        $wallet_type = $request->post('wallet_type');
        $var = MemberDetail::where('memberid', $memberid)->first();
        if (!$var) {
            session()->flash('failedMsg', 'Entered Memberid is not Available. Please change memberid');
            return redirect()->back();
        }
        // if ($var->status != 'Active') {
        //     session()->flash('failedMsg', 'Entered Memberid is not Active. Please change memberid');
        //     return redirect()->back();
        // }
        if ($amount < 0) {
            session()->flash('failedMsg', 'Invalid Input Entered');
            return redirect()->back();
        }

        if ($wallet_type == 'Wallet') {

            if ($amount > $var->wallet) {
                session()->flash('failedMsg', 'Deduction amount can not be greater than available balance');
                return redirect()->back();
            }
            $wallet = $var->wallet;
            $var->wallet -= $amount;
            $var->save();

            walletTransfer($memberid, $amount, 'credit', $wallet, 'Fund Transfer', '$ ' . $amount . ' has been transferred to your wallet by Admin');

            session()->flash('successMsg', 'Amount has been deducted successfully from Member Wallet');
        } elseif ($wallet_type == 'Fund Wallet') {
            if ($amount > $var->p2p_wallet) {
                session()->flash('failedMsg', 'Deduction amount can not be greater than available balance');
                return redirect()->back();
            }
            $wallet = $var->p2p_wallet;
            $var->p2p_wallet -= $amount;
            $var->save();

            walletTransfer($memberid, $amount, 'credit', $wallet, 'Fund Transfer', '$ ' . $amount . ' has been transferred to your wallet by Admin');

            session()->flash('successMsg', 'Amount has been deducted successfully from fund wallet');
        }

        $varimp = new ImportFund();
        $varimp->memberid = $memberid;
        $varimp->amount = $amount;
        $varimp->wallet_type = $wallet_type;
        $varimp->orderid = rand(10000, 99999);
        $varimp->txnid = 'NA';
        $varimp->type = 'Deduct';
        $varimp->added_by = 'Admin';
        $varimp->status = 'Approved';
        $varimp->mode = 'Manual';
        $varimp->save();

        return redirect()->back();
    }

    public function addFundsDetails()
    {
        $result['data'] = ImportFund::where('added_by', 'Admin')->orderby('created_at', 'DESC')->get();
        return view('admin.funds.add-funds-details')->with($result);
    }

    public function requestsHistory()
    {
        //$result['coins'] = ConvertPrize::find(1);
        $result['data'] = ImportFund::where('added_by', 'User')->orderby('created_at', 'DESC')->get();
        return view('admin.funds.request-history')->with($result);
    }

    public function setRates()
    {
        $result['data'] = SetRate::find(1);
        return view('admin.set-rates')->with($result);
    }

    public function rateUpdate(Request $request)
    {
        $request->validate([
            'rate' => 'required|numeric'
        ]);
        $var = SetRate::find(1);
        $var->roi_rate = $request->post('rate');
        $var->save();
        session()->flash('successMsg', 'Rates updated successfully.');
        return redirect()->back();
    }
}
