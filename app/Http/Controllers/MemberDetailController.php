<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\DashMessage;
use App\Models\MemberDetail;
use App\Models\SetRate;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class MemberDetailController extends Controller
{
    function index()
    {
        if (session()->has('MEMBER_ID')) {
            return redirect('member/dashboard');
        } else {
            $str1 = rand(1, 15);
            $str2 = rand(1, 15);

            session()->put('str1', $str1);
            session()->put('str2', $str2);
            return view('member.signin');
        }
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'memberid' => 'required',
            'password' => 'required',
            'login-captcha' => 'required'
        ]);
        $memberid = $request->post('memberid');
        $password = $request->post('password');
        $captcha = $request->post('login-captcha');

        $str1 = session('str1');
        $str2 = session('str2');
        $total = $str1 + $str2;
        if ($total != $captcha) {
            session()->flash('failedmsg', 'Invalid captcha input');
            return redirect('/member');
        } else {
            session()->forget(['str1', 'str2']);
        }

        $result = MemberDetail::where('memberid', $memberid)->first();
        if ($result) {
            $hashed = $result->password;
            if (Hash::check($password, $hashed)) {
                $request->session()->put('MEMBER_LOGIN', true);
                $request->session()->put('MEMBER_ID', $result->memberid);
                $request->session()->put('show_popup_modal', true);
                return redirect('member/dashboard');
            } else {
                session()->flash('failedmsg', 'Please enter valid Password to login');
                return redirect('/member');
            }
        } else {
            session()->flash('failedmsg', 'Please enter valid Memberid to login');
            return redirect('/member');
        }
    }

    public function addressValidate(Request $request)
    {
        $address = $request->post('address');
        $var = MemberDetail::where('member_wallet', $address)->first();
        if ($var) {
            session()->put('address', $address);
            session()->put('MEMBER_ID', $var->memberid);
            return response()->json([
                'code' => 1,
            ]);
        } else {
            session()->put('address', $address);
            return response()->json([
                'code' => 0,
            ]);
        }
    }

    public function signup(Request $request, $id)
    {
        $sponsorid['sdata'] = $id;
        $sponsorid['countries'] = Country::all();
        return view('member.signup')->with($sponsorid);
    }

   public function userregister(Request $request)
    {

        $request->validate([
            'sponsor' => 'required|exists:member_details,memberid',
            // 'position' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'required|min:10|max:10',
            'country' => 'required',
            'password' => 'required|required_with:confirm_password|same:confirm_password|min:6',
            'confirm_password' => 'required',
        ]);
        $sponsorid = strtoupper($request->post('sponsor'));
        $spon = MemberDetail::where('memberid', $sponsorid)->first();
        $email = $request->post('email');
        // $country = $request->post('country');

        // if ($spon->status != 'Active') {
        //     session()->flash('failedMsg', 'Your account is not activated.');
        //     return redirect()->back();
        // }
        
        // $emailCount = MemberDetail::where('email', $email)->count();
        // if ($emailCount >= 3) {
        //     session()->flash('failedMsg', 'This email has already been used 3 times. Please use another email.');
        //     return redirect()->back();
        // }
        
        $var  = new MemberDetail();
        $var->memberid = userId();
        $var->sponsorid = $sponsorid;
        $var->name = $request->post('name');
        $var->email = $email;
        $var->mobile = $request->post('mobile');
        $password =  $request->post('password');
        $var->password = Hash::make($password);
        $txnpass = rand(100000, 999999);
        // $var->p2p_wallet = 100000;
        $var->txn_password = Hash::make($txnpass);
        $var->country = $request->post('country');
        $var->save();
        
        
        updateUpline($sponsorid, $var->memberid);
        
        $data = MemberDetail::find($var->id);
        $userid = $data->memberid;
        $email = $data->email;
        $name = $data->name;
        $mobile = $data->mobile;
        if ($email != '') {
            $mailData = [
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'memberid' => $userid,
                'password' => $password,
                'txn_password' => $txnpass
            ];
            $user['to'] = $email;
            Mail::send('member.mails.mail', $mailData, function ($message) use ($user) {

                $message->to($user['to']);
                $message->subject('Demo Member Login Credentials');
            });
        }
        $request->session()->put('name', $request->post('name'));
        $request->session()->put('memberid', $userid);
        $request->session()->put('email', $email);
        $request->session()->put('mobile', $mobile);
        $request->session()->put('country', $request->post('country'));
        $request->session()->put('password', $request->post('password'));
        $request->session()->put('txn_password', $txnpass);
        session()->flash('successMsg', 'Account has been created Successfully!.');
        return redirect('member/account-created');
    }
    public function accountCreated()
    {
        return view('member.account-created');
    }

    public function dashboard(Request $request)
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['dashMsg'] = DashMessage::orderby('rank', 'asc')->get();
        $result['rate'] = SetRate::find(1);

        $totalCapping = 0.0;
        $achievedCapping = 0.0;

        if (!empty($memberid) && !empty($result['data'])) {
            $totalCapping = (float) capping($memberid);
            $achievedCapping = (float) Income3xachieved($memberid);
        }

        $remainingCapping = max(0.0, $totalCapping - $achievedCapping);
        $cappingPercentage = ($totalCapping > 0)
            ? min(100.0, max(0.0, ($achievedCapping / $totalCapping) * 100.0))
            : 0.0;

        $result['totalCapping'] = $totalCapping;
        $result['achievedCapping'] = $achievedCapping;
        $result['remainingCapping'] = $remainingCapping;
        $result['cappingPercentage'] = $cappingPercentage;

        $popupImage = Setting::get('dashboard_popup_image', '');
        $popupStatus = Setting::get('dashboard_popup_status', '1');
        $popupTitle = Setting::get('dashboard_popup_title', '');
        $popupLink = Setting::get('dashboard_popup_link', '');

        $result['popupImage'] = ($popupStatus == '1' && !empty($popupImage) && file_exists(public_path('uploads/' . $popupImage))) ? $popupImage : null;
        $result['popupTitle'] = $popupTitle;
        $result['popupLink'] = $popupLink;

        return view('member.dashboard')->with($result);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('MEMBER_LOGIN');
        $request->session()->forget('MEMBER_ID');

        return redirect('/member');
    }

    public function getSponname(Request $request)
    {
        $sponsorid = $request->post('sponsorid');
        if ($sponsorid != '') {
            $data = MemberDetail::where('memberid', $sponsorid)->first();
            if ($data) {

                if ($data['status'] == 'Temp') {
                    return response()->json([
                        'code' => 0,
                        'data' => '<span style="color:red">This SponsorId is not Active</span>'
                    ]);
                } elseif ($data['status'] == 'Deactive') {
                    return response()->json([
                        'code' => 0,
                        'data' => '<span style="color:red">This Sponsor Id is Deactive. Please change sponsor id</span>'
                    ]);
                } elseif ($data['status'] == 'Active') {
                    return response()->json([
                        'code' => 1,
                        'data' => '<span class="text-success">Name : ' . $data['name'] . '</span>'
                    ]);
                }
            } else {
                return response()->json([
                    'code' => 1,
                    'data' => '<span style="color:red">No Data Found with this Id</span>'
                ]);
            }
        } else {
            return response()->json([
                'code' => 1,
                'data' => ''
            ]);
        }
    }

    public function getMemberid(Request $request)
    {
        $memberid = $request->post('memberid');
        if ($memberid != '') {
            $data = MemberDetail::where('memberid', $memberid)->first();
            if ($data) {
                if ($data->status == 'Temp') {
                    return response()->json([
                        'code' => 1,
                        'data' => '<span class="text-success">Member Name :' . $data['name'] . '</span>'
                    ]);
                } else {
                    return response()->json([
                        'code' => 0,
                        'data' => '<span class="text-danger">This Memberid is already Active.</span>'
                    ]);
                }
            } else {
                return response()->json([
                    'code' => 0,
                    'data' => '<span class="text-danger">No Data Found with this Id</span>'
                ]);
            }
        } else {
            return response()->json([
                'code' => 0,
                'data' => ''
            ]);
        }
    }

    public function getMember(Request $request)
    {
        $memberid = $request->post('memberid');
        if ($memberid != '') {
            $data = MemberDetail::where('memberid', $memberid)->first();
            if ($data) {
                return response()->json([
                    'code' => 1,
                    'data' => '<span class="text-success">Member Name :' . $data['name'] . '</span>'
                ]);
            } else {
                return response()->json([
                    'code' => 0,
                    'data' => '<span class="text-danger">No Data Found with this Id</span>'
                ]);
            }
        } else {
            return response()->json([
                'code' => 0,
                'data' => ''
            ]);
        }
    }

    public function getUsername(Request $request)
    {
        $username = $request->post('username');
        if ($username != '') {

            $data = MemberDetail::where('memberid', $username)->first();
            if ($data) {

                return response()->json([
                    'code' => 0,
                    'data' => '<span class="text-danger">This username is not available. Please change username</span>'
                ]);
            } else {
                if ($username == 'Root') {
                    return response()->json([
                        'code' => 0,
                        'data' => '<span class="text-danger">This Username can not be used.</span>'
                    ]);
                } else {
                    return response()->json([
                        'code' => 1,
                        'data' => '<span class="text-white">This Username is available</span>'
                    ]);
                }
            }
        } else {
            return response()->json([
                'code' => 1,
                'data' => ''
            ]);
        }
    }

    public function userSummary()
    {
        $memberid = session('MEMBER_ID');
        $result['dashMsg'] = DashMessage::orderby('rank', 'asc')->get();
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        return view('member.profile.user-summary')->with($result);
    }

    public function insertPdata(Request $request)
    {
        //print_r($request->post());
        $id = $request->post('id');
        $var = MemberDetail::find($id);
        $var->name = $request->post('name');
        // $var->email = $request->post('email');
        // $var->country_code = $request->post('country_code');
        $var->mobile = $request->post('mobile');
        // $var->gender = $request->post('gender');
        // $var->dob = $request->post('dob');
        $var->country = $request->post('country');
        if ($request->hasfile('profile_image')) {
            $file = $request->file('profile_image');
            $extension = $file->getClientOriginalExtension();
            if ($extension != 'png' && $extension != 'jpg' && $extension != 'jpeg') {
                session()->flash('failedMsg', 'Allowed image type is jpg, jpeg, png. Please change image type');
                return redirect()->back();
            }
            $filename = time() . '.' . $extension;
            $file->move(public_path('uploads'), $filename);
            $var->profile_image = $filename;
        }
        $var->save();

        session()->flash('successMsg', 'Profile Details have been updated');
        return redirect()->back();
    }

    public function security()
    {
        $memberid = session('MEMBER_ID');
        $result['dashMsg'] = DashMessage::orderby('rank', 'asc')->get();
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        return view('member.profile.security')->with($result);
    }

    public function passChange(Request $request)
    {
        $request->validate([
            'password' => 'required|required_with:password_confirm|same:password_confirm|min:6',
            'password_confirm' => 'min:6'
        ]);

        $id = $request->post('id');
        $var = MemberDetail::find($id);
        $var->password = Hash::make($request->post('password'));
        $var->save();

        session()->flash('successMsg_pass', 'Password has been updated successfully');
        return redirect()->back();
    }

    public function txnpassChange(Request $request)
    {
        $request->validate([
            'txn_password' => 'required|required_with:txn_password_confirm|same:txn_password_confirm|min:6',
            'txn_password_confirm' => 'min:6'
        ]);

        $id = $request->post('id');
        $var = MemberDetail::find($id);
        $var->txn_password = Hash::make($request->post('txn_password'));
        $var->save();

        session()->flash('successMsg', 'Transaction Password has been updated successfully');
        return redirect()->back();
    }

    public function walletAddress()
    {
        $memberid = session('MEMBER_ID');
        $result['dashMsg'] = DashMessage::orderby('rank', 'asc')->get();
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        return view('member.profile.wallet-address')->with($result);
    }

    public function insertWalletAddress(Request $request)
    {
        $id = $request->post('id');

        $var = MemberDetail::find($id);
        $var->wallet_address = $request->post('wallet_address');
        $var->kyc_status = 'Submitted';
        $var->save();

        session()->flash('msgsucc', 'Wallet Address have been inserted successfully!');
        return redirect()->back();
    }

    public function memforgetPassword()
    {
        return view('member.forget-password');
    }

    public function memretrivePassword(Request $request)
    {
        $request->validate([
            'email' => 'required'
        ]);
        $email = $request->post('email');

        $data = MemberDetail::where('email', $email)->first();
        if ($data) {

            $name = $data['name'];
            $email = $data['email'];
            $memberid = $data['memberid'];
            $password = rand(100000, 999999);
            $data->password = Hash::make($password);
            $data->save();

            if ($email != '') {
                $mailData = [
                    'name' => $name,
                    'email' => $email,
                    'memberid' => $memberid,
                    'password' => $password
                ];
                $user['to'] = $email;
                Mail::send('member.mails.forget-password-mail', $mailData, function ($message) use ($user) {

                    $message->to($user['to']);
                    $message->subject('Demo Member Account Password');
                });
            }
            session()->flash('succMsg', 'Your Account Password has been sent to your registered email.');
            return redirect()->back();
        } else {
            session()->flash('forgetPassErr', 'Entered Email Address is not registered with us!');
            return redirect()->back();
        }
    }

    public function createUser(Request $request, $sponsorid, $side)
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['userId'] = userId();
        $result['sdata'] = $memberid;
        $result['side'] = $side;
        $result['countries'] = Country::all();
        return view('member.create-user')->with($result);
    }

    public function userCreate(Request $request)
    {

        $request->validate([
            'sponsor' => 'required|exists:member_details,memberid',
            'memberid' => 'required|unique:member_details,memberid',
            'position' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'required|min:10|max:10',
            'country' => 'required',
            'password' => 'required',
        ]);
        $sponsorid = session('MEMBER_ID');;
        $spon = MemberDetail::where('memberid', $sponsorid)->first();
        $memberid = $request->post('memberid');
        $email = $request->post('email');
        $country = $request->post('country');

        // if ($spon->status != 'Active') {
        //     session()->flash('failedMsg', 'Your account is not activated.');
        //     return redirect()->back();
        // }
        $count = MemberDetail::where('email', $email)->count();
        if ($count >= 3) {
            session()->flash('failedMsg', 'This email is already registered');
            return redirect()->back();
        }
        $var  = new MemberDetail();
        $var->memberid = $memberid;
        $var->sponsorid = $sponsorid;
        $var->name = $request->post('name');
        $var->email = $email;
        $var->mobile = $request->post('mobile');
        $password =  $request->post('password');
        $var->password = Hash::make($password);
        $txnpass = rand(100000, 999999);
        $var->txn_password = Hash::make($txnpass);
        $var->country = $request->post('country');
        $var->position = $request->post('position');
        $var->save();

        // addPlacementId($var->memberid, $request->post('sponsor'), $request->post('position'));
        updateUpline($sponsorid, $var->memberid);

        $data = MemberDetail::find($var->id);
        $userid = $data->memberid;
        $email = $data->email;
        $name = $data->name;
        $mobile = $data->mobile;
        $placementid = $data->placementid;
        // binary_count_update($userid, $placementid, $request->post('position'));
        if ($email != '') {
            $mailData = [
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'memberid' => $userid,
                'password' => $password,
                'txn_password' => $txnpass
            ];
            $user['to'] = $email;
            Mail::send('member.mails.mail', $mailData, function ($message) use ($user) {

                $message->to($user['to']);
                $message->subject('Demo Member Login Credentials');
            });
        }
        $request->session()->put('name', $request->post('name'));
        $request->session()->put('memberid', $userid);
        $request->session()->put('email', $email);
        $request->session()->put('mobile', $mobile);
        $request->session()->put('country', $country);
        $request->session()->put('password', $request->post('password'));
        $request->session()->put('txn_password', $txnpass);
        session()->flash('successMsg', 'Account has been created Successfully!.');
        return redirect()->back();
    }

    public function getMemberActive(Request $request)
    {
        $memberid = $request->input('memberid');

        $var = MemberDetail::where('memberid', $memberid)->first();

        if ($var) {
            $var->status = 'Active';
            $var->save();

            session()->flash('successMsg', 'Your status is active.');
            return redirect()->back();
        } else {
            session()->flash('successMsg', 'This id is not exist.');
            return redirect()->back();
        }
    }
    
    public function trading()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        return view('member.trading.arbitrage-trading')->with($result);
    }
}
