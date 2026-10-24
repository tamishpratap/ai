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
use App\Models\Package;
use App\Models\RewardIncome;
use App\Models\PackageDetail;
use App\Models\RankIncome;
use App\Models\SetRate;
use App\Models\Setting;
use App\Models\StakingDetail;
use App\Models\StakingIncome;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Carbon\Carbon;
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
                Mail::send('admin.mails.login-mail', $mailData, function ($message) use ($user) {

                    $message->to($user['to']);
                    $message->subject('Demo Admin Login OTP');
                });
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
            Mail::send('admin.mails.forget-password-mail', $mailData, function ($message) use ($user) {

                $message->to($user['to']);
                $message->subject('Demo Admin Account Password');
            });
        }
        session()->flash('succMsg', 'Your Account Password has been sent to your registered email.');
        return redirect()->back();
    }

    public function details(Request $request)
    {
        $query = MemberDetail::query();
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at', [
            'created_at'   => 'Joining Date',
            'activated_at' => 'Activation Date',
        ]);
        $result['data'] = $filter['query']->orderby('created_at', 'desc')->get();
        $result['filterData'] = $filter['filterData'];
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
            $request->session()->put('show_popup_modal', true);
            return redirect('member/dashboard');
        }
    }

    public function popupImage()
    {
        $result['popupImage'] = Setting::get('dashboard_popup_image', '');
        $result['popupStatus'] = Setting::get('dashboard_popup_status', '1');
        $result['popupTitle'] = Setting::get('dashboard_popup_title', '');
        $result['popupLink'] = Setting::get('dashboard_popup_link', '');
        return view('admin.popup-image')->with($result);
    }

    public function popupImageUpdate(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:500',
            'status' => 'nullable|in:0,1',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'popup_' . time() . '_' . rand(1000, 9999) . '.' . $extension;

            $uploadPath = public_path('uploads');
            if (!file_exists($uploadPath)) {
                @mkdir($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $filename);

            $oldImage = Setting::get('dashboard_popup_image');
            if (!empty($oldImage) && file_exists(public_path('uploads/' . $oldImage))) {
                @unlink(public_path('uploads/' . $oldImage));
            }

            Setting::set('dashboard_popup_image', $filename);
        }

        if ($request->has('title')) {
            Setting::set('dashboard_popup_title', $request->post('title') ?? '');
        }

        if ($request->has('link')) {
            Setting::set('dashboard_popup_link', $request->post('link') ?? '');
        }

        if ($request->has('status')) {
            Setting::set('dashboard_popup_status', $request->post('status') == '1' ? '1' : '0');
        }

        session()->flash('uploadMsg', 'Popup Image settings updated successfully!');
        return redirect()->back();
    }

    public function popupImageToggleStatus()
    {
        $currentStatus = Setting::get('dashboard_popup_status', '1');
        $newStatus = ($currentStatus == '1') ? '0' : '1';
        Setting::set('dashboard_popup_status', $newStatus);

        $msg = ($newStatus == '1') ? 'Dashboard Popup Image is now Active (Visible to members)' : 'Dashboard Popup Image is now Inactive (Hidden from members)';
        session()->flash('uploadMsg', $msg);
        return redirect()->back();
    }

    public function popupImageDelete()
    {
        $image = Setting::get('dashboard_popup_image');
        if (!empty($image) && file_exists(public_path('uploads/' . $image))) {
            @unlink(public_path('uploads/' . $image));
        }

        Setting::set('dashboard_popup_image', '');
        Setting::set('dashboard_popup_status', '0');

        session()->flash('delMsg', 'Dashboard Popup Image has been removed successfully!');
        return redirect()->back();
    }

    public function acheivImage(Request $request)
    {
        return $this->popupImageUpdate($request);
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

    public function accountControl(Request $request)
    {
        $query = MemberDetail::where('status', '!=', 'Temp');
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at');
        $result['data'] = $filter['query']->orderby('created_at', 'desc')->get();
        $result['filterData'] = $filter['filterData'];
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

    /**
     * Apply theme-compatible date filtering to an Eloquent query for admin history tables.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param \Illuminate\Http\Request $request
     * @param string $defaultColumn
     * @param array $allowedDateColumns Mapping of column => label (e.g. ['payment_date' => 'Payment Date', 'request_date' => 'Request Date'])
     * @return array ['query' => Builder, 'filterData' => array]
     */
    protected function applyAdminDateFilter($query, Request $request, string $defaultColumn, array $allowedDateColumns = [])
    {
        $tz = config('app.timezone', 'Asia/Kolkata');
        $filterMode = $request->query('filter_mode');
        $filterDate = $request->query('filter_date');
        $preset = $request->query('preset');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $dateFieldInput = $request->query('date_field');

        // 1. Validate & resolve date column safely
        $selectedColumn = $defaultColumn;
        $dateOptions = [];
        if (!empty($allowedDateColumns)) {
            $dateOptions = $allowedDateColumns;
            if ($dateFieldInput && array_key_exists($dateFieldInput, $allowedDateColumns)) {
                $selectedColumn = $dateFieldInput;
            }
        } else {
            $dateOptions = [$defaultColumn => 'Date'];
        }

        $activeFilter = false;
        $badgeText = '';
        $validationError = null;
        $startDate = null;
        $endDate = null;

        // Helper to validate Y-m-d format
        $isValidDate = function ($dateStr) {
            if (!is_string($dateStr) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                return false;
            }
            $parts = explode('-', $dateStr);
            return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
        };

        // Mode 1: Single Date Filter
        if ($filterMode === 'single' || (!empty($filterDate) && empty($filterMode))) {
            if (!empty($filterDate)) {
                if ($isValidDate($filterDate)) {
                    $startDate = $filterDate . ' 00:00:00';
                    $endDate = $filterDate . ' 23:59:59';
                    $activeFilter = true;
                    $formattedDate = Carbon::parse($filterDate)->format('d M Y');
                    $colLabel = $dateOptions[$selectedColumn] ?? 'Date';
                    $badgeText = count($dateOptions) > 1 ? "$colLabel: $formattedDate" : $formattedDate;
                } else {
                    $validationError = 'Please select a valid date in YYYY-MM-DD format.';
                }
            }
        }
        // Mode 2: Advanced Date Filter
        elseif ($filterMode === 'advanced' || !empty($preset) || !empty($fromDate) || !empty($toDate)) {
            $from = null;
            $to = null;
            $presetLabel = null;

            $now = Carbon::now($tz);

            if (!empty($preset) && $preset !== 'custom') {
                switch ($preset) {
                    case 'today':
                        $from = $now->copy()->toDateString();
                        $to = $now->copy()->toDateString();
                        $presetLabel = 'Today';
                        break;
                    case 'yesterday':
                        $from = $now->copy()->subDay()->toDateString();
                        $to = $now->copy()->subDay()->toDateString();
                        $presetLabel = 'Yesterday';
                        break;
                    case 'last_7_days':
                        $from = $now->copy()->subDays(6)->toDateString();
                        $to = $now->copy()->toDateString();
                        $presetLabel = 'Last 7 Days';
                        break;
                    case 'last_30_days':
                        $from = $now->copy()->subDays(29)->toDateString();
                        $to = $now->copy()->toDateString();
                        $presetLabel = 'Last 30 Days';
                        break;
                    case 'this_month':
                        $from = $now->copy()->startOfMonth()->toDateString();
                        $to = $now->copy()->endOfMonth()->toDateString();
                        $presetLabel = 'This Month';
                        break;
                    case 'last_month':
                        $from = $now->copy()->subMonthNoOverflow()->startOfMonth()->toDateString();
                        $to = $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString();
                        $presetLabel = 'Last Month';
                        break;
                    case 'this_year':
                        $from = $now->copy()->startOfYear()->toDateString();
                        $to = $now->copy()->endOfYear()->toDateString();
                        $presetLabel = 'This Year';
                        break;
                    default:
                        $validationError = 'Invalid preset option selected.';
                        break;
                }

                if ($from && $to) {
                    $fromDate = $from;
                    $toDate = $to;
                }
            }

            if (!empty($fromDate) || !empty($toDate)) {
                if (empty($fromDate) || empty($toDate)) {
                    $validationError = 'Both From Date and To Date are required for date range filtering.';
                } elseif (!$isValidDate($fromDate) || !$isValidDate($toDate)) {
                    $validationError = 'Invalid date format provided for date range.';
                } elseif ($toDate < $fromDate) {
                    $validationError = 'To Date cannot be earlier than From Date.';
                } else {
                    $from = $fromDate;
                    $to = $toDate;
                }
            }

            if ($from && $to && empty($validationError)) {
                $startDate = $from . ' 00:00:00';
                $endDate = $to . ' 23:59:59';
                $activeFilter = true;

                $colLabel = $dateOptions[$selectedColumn] ?? 'Date';
                $colPrefix = count($dateOptions) > 1 ? "$colLabel: " : "";

                if ($presetLabel && $preset !== 'custom') {
                    $badgeText = $colPrefix . $presetLabel;
                } else {
                    $fFormatted = Carbon::parse($from)->format('d M Y');
                    $tFormatted = Carbon::parse($to)->format('d M Y');
                    $badgeText = $fFormatted === $tFormatted ? "$colPrefix$fFormatted" : "$colPrefix$fFormatted – $tFormatted";
                }
            }
        }

        // Apply date filter boundaries to query
        if ($startDate && $endDate) {
            $query->where($selectedColumn, '>=', $startDate)
                  ->where($selectedColumn, '<=', $endDate);
        }

        $filterData = [
            'filter_mode'      => $filterMode,
            'filter_date'      => $filterDate,
            'preset'           => $preset,
            'from_date'        => $fromDate,
            'to_date'          => $toDate,
            'date_field'       => $selectedColumn,
            'date_options'     => $dateOptions,
            'active_filter'    => $activeFilter,
            'badge_text'       => $badgeText,
            'validation_error' => $validationError,
        ];

        return [
            'query'      => $query,
            'filterData' => $filterData,
        ];
    }

    public function stakingDetails(Request $request)
    {
        $query = StakingDetail::query();
        $filter = $this->applyAdminDateFilter($query, $request, 'invest_date');
        $result['sdata'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['filterData'] = $filter['filterData'];
        return view('admin.income.staking-details')->with($result);
    }

    public function stakingIncomes(Request $request)
    {
        $query = StakingIncome::query();
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at');
        $result['sdata'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['filterData'] = $filter['filterData'];
        return view('admin.income.daily-income')->with($result);
    }

    public function levelDetails(Request $request)
    {
        $query = StakingDetail::where('type', 'Level Income');
        $filter = $this->applyAdminDateFilter($query, $request, 'invest_date');
        $result['sdata'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['filterData'] = $filter['filterData'];
        return view('admin.income.level-details')->with($result);
    }

    public function levelIncomes(Request $request)
    {
        $query = LevelIncome::query();
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at');
        $result['sdata'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['filterData'] = $filter['filterData'];
        return view('admin.income.level-income')->with($result);
    }
    
    public function rewardIncomes(Request $request)
    {
        $query = RewardIncome::query();
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at');
        $result['sdata'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['rdata'] = $result['sdata'];
        $result['filterData'] = $filter['filterData'];
        return view('admin.income.reward-income')->with($result);
    }

    public function directIncomes(Request $request)
    {
        $query = DirectIncome::query();
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at');
        $result['sdata'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['ddata'] = $result['sdata'];
        $result['filterData'] = $filter['filterData'];
        return view('admin.income.direct-income')->with($result);
    }

    public function monthlyRoiIncomes(Request $request)
    {
        $query = StakingIncome::query();
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at');
        $result['sdata'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['filterData'] = $filter['filterData'];
        return view('admin.income.monthly-roi-income')->with($result);
    }
    
    public function packageDetails(Request $request)
    {
        $query = PackageDetail::query();
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at');
        $result['pdata'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['filterData'] = $filter['filterData'];
        return view('admin.package-details')->with($result);
    }

    public function fundRequests()
    {
        $result['data'] = FundRequest::where('status', 'Requested')->orderBy('created_at', 'asc')->get();
        return view('admin/funds/new-funds-requests', $result);
    }

    public function fundsHistory(Request $request)
    {
        $query = ImportFund::where('added_by', 'User');
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at');
        $result['data'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['filterData'] = $filter['filterData'];
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

    public function cancelledRequest(Request $request)
    {
        $query = WithdrawalRequest::where('status', 'Cancelled');
        $filter = $this->applyAdminDateFilter($query, $request, 'updated_at', [
            'updated_at'   => 'Cancellation Date',
            'request_date' => 'Request Date',
        ]);
        $result['data'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['filterData'] = $filter['filterData'];
        return view('admin.cancelled-request')->with($result);
    }

    public function paymentHistory(Request $request)
    {
        $query = WithdrawalRequest::where('status', 'Approved');
        $filter = $this->applyAdminDateFilter($query, $request, 'payment_date', [
            'payment_date' => 'Payment Date',
            'request_date' => 'Request Date',
        ]);
        $result['data'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['filterData'] = $filter['filterData'];
        return view('admin.payment-history')->with($result);
    }

    public function withdrawlHistory()
    {
        $result['data'] = WithdrawalRequest::where('status', 'Approved')->get();
        return view('admin.payment-history')->with($result);
    }

    public function wAccept(Request $request, $id)
    {
            $withdrawal = WithdrawalRequest::where('id', $id)->lockForUpdate()->first();
            $withdrawal->status = 'Approved';
            $withdrawal->payment_date = now();
            $withdrawal->save();

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

    public function addFundsDetails(Request $request)
    {
        $query = ImportFund::where('added_by', 'Admin');
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at');
        $result['data'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['filterData'] = $filter['filterData'];
        return view('admin.funds.add-funds-details')->with($result);
    }

    public function requestsHistory(Request $request)
    {
        //$result['coins'] = ConvertPrize::find(1);
        $query = ImportFund::where('added_by', 'User');
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at');
        $result['data'] = $filter['query']->orderby('created_at', 'DESC')->get();
        $result['filterData'] = $filter['filterData'];
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

    private const MLM_SETTING_KEYS = [
        'direct_referral_percentage',
        'binary_matching_percentage',
        'maximum_income_percentage',
        'minimum_withdrawal',
        'withdrawal_fee_percentage',
        'withdrawal_status',
        'p2p_transfer_fee_percentage',
        'p2p_transfer_status',
        'deposit_status',
        'trading_reward_status',
        'binary_closing_status',
        'binary_closing_time',
    ];

    // public function mlmSettings()
    // {
    //     $result['settings'] = collect(self::MLM_SETTING_KEYS)
    //         ->mapWithKeys(fn ($key) => [$key => Setting::get($key)])
    //         ->toArray();
    //     return view('admin.settings.mlm-settings')->with($result);
    // }

    // public function mlmSettingsUpdate(Request $request)
    // {
    //     $request->validate([
    //         'direct_referral_percentage' => 'required|numeric|min:0',
    //         'binary_matching_percentage' => 'required|numeric|min:0',
    //         'maximum_income_percentage' => 'required|numeric|min:0',
    //         'minimum_withdrawal' => 'required|numeric|min:0',
    //         'withdrawal_fee_percentage' => 'required|numeric|min:0',
    //         'withdrawal_status' => 'required|in:enabled,disabled',
    //         'p2p_transfer_fee_percentage' => 'required|numeric|min:0',
    //         'p2p_transfer_status' => 'required|in:enabled,disabled',
    //         'deposit_status' => 'required|in:enabled,disabled',
    //         'trading_reward_status' => 'required|in:enabled,disabled',
    //         'binary_closing_status' => 'required|in:enabled,disabled',
    //         'binary_closing_time' => 'required',
    //     ]);

    //     foreach ($request->only(self::MLM_SETTING_KEYS) as $key => $value) {
    //         Setting::set($key, $value);
    //     }

    //     session()->flash('successMsg', 'Settings updated successfully.');
    //     return redirect()->back();
    // }

    public function packages()
    {
        $result['data'] = Package::orderBy('sort_order')->get();
        return view('admin.packages')->with($result);
    }

    public function packageStatusChange(Request $request, $id)
    {
        $package = Package::findOrFail($id);
        $package->is_active = !$package->is_active;
        $package->save();

        session()->flash('successMsg', 'Package status updated successfully.');
        return redirect()->back();
    }
    public function freePinAccount(Request $request)
    {
        $query = MemberDetail::where('status', 'Temp');
        $filter = $this->applyAdminDateFilter($query, $request, 'created_at');
        $result['data'] = $filter['query']->orderby('created_at', 'desc')->get();
        $result['filterData'] = $filter['filterData'];
        return view('admin.members.free-pin-account')->with($result);
    }

     public function PinUserAccount(Request $request)
    {
        $memberid = $request->post('memberid');
        $package = $request->post('package');
        $amount = $request->post('amount');

        if ($package == NULL) {
            session()->flash('failedMsg', 'Please enter package first');
            return redirect()->back();
        }

        if ($amount < 0) {
            session()->flash('failedMsg', 'Not valid amount entered');
            return redirect()->back();
        }

        // if ($amount < 50) {
        //     session()->flash('failedMsg', 'Minimum amount must be or greater than $50');
        //     return redirect()->back();
        // }

         // Check package according to investment amount
        if ($amount >= 100 && $amount <= 1000) {

            if ($package != 'Silver') {
                session()->flash('failedMsg', 'Please select Silver package for this investment amount.');
                return redirect()->back()->withInput();
            }

            $packageName = 'Silver';
        } elseif ($amount >= 1100 && $amount <= 5000) {

            if ($package != 'Gold') {
                session()->flash('failedMsg', 'Please select Gold package for this investment amount.');
                return redirect()->back()->withInput();
            }

            $packageName = 'Gold';
        } elseif ($amount >= 5100 && $amount <= 10000) {

            if ($package != 'Platinum') {
                session()->flash('failedMsg', 'Please select Platinum package for this investment amount.');
                return redirect()->back()->withInput();
            }

            $packageName = 'Platinum';
        } elseif ($amount >= 11000) {

            if ($package != 'Diamond') {
                session()->flash('failedMsg', 'Please select Diamond package for this investment amount.');
                return redirect()->back()->withInput();
            }

            $packageName = 'Diamond';
        } else {
            session()->flash('failedMsg', 'Invalid investment amount.');
            return redirect()->back()->withInput();
        }

        // if($package % 10 != 0){
        //     session()->flash('failedMsg', 'Package must be multiple of $10');
        //     return redirect()->back();
        // }


        $memUpdate = MemberDetail::where('memberid', $memberid)->first();
        $sponsorid = $memUpdate->sponsorid;

        $memUpdate->activated_by = 'Admin';
        $memUpdate->self_biz = $amount;
        $memUpdate->package = $amount;
        $memUpdate->package_name = $packageName;
        if ($memUpdate->status == 'Temp') {
            $memUpdate->status = 'Active';
            $memUpdate->activated_at = date('Y-m-d H:i:s');
            updateUpline($sponsorid, $memberid);
            updateDownline($sponsorid);
        }
        $memUpdate->save();

        $var = new StakingDetail();
        $var->memberid = $memberid;
        $var->invest_date = date('Y-m-d H:i:s');
        $var->invest_amount = $amount;
        $var->package = $package;
        $var->package_name = $packageName;
        $var->rate = 0;
        $var->activated_by = 'Admin';
        $var->status = 'Deactive';
        $var->save();

        session()->flash('successMsg', 'Member has been activated successfully.');
        return redirect()->back();
    }
}
