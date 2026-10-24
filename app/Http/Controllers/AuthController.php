<?php

namespace App\Http\Controllers;

use App\Models\MemberDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showAuthForm(): View|RedirectResponse
    {
        if (session()->has('MEMBER_ID') && MemberDetail::where('id', session('MEMBER_ID'))->exists()) {
            return redirect()->route('member.ai.voice-assistant');
        }

        return view('frontend.ai.auth');
    }

    public function register(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:150|unique:member_details,email',
            'mobile' => 'required|string|min:8|max:20|unique:member_details,mobile',
            'password' => 'required|string|min:4|max:100',
        ], [
            'name.required' => 'Please enter your full name.',
            'email.required' => 'Please enter your email address.',
            'email.unique' => 'This email is already registered. Please sign in.',
            'mobile.required' => 'Please enter your mobile number.',
            'mobile.unique' => 'This mobile number is already registered. Please sign in.',
            'password.required' => 'Please enter a password.',
            'password.min' => 'Password must be at least 4 characters.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput()->with('tab', 'signup');
        }

        // Generate unique member code
        $memberCode = 'AI'.mt_rand(100000, 999999);
        while (MemberDetail::where('memberid', $memberCode)->exists()) {
            $memberCode = 'AI'.mt_rand(100000, 999999);
        }

        $member = MemberDetail::create([
            'name' => trim($request->string('name')),
            'email' => trim(strtolower($request->string('email'))),
            'mobile' => trim($request->string('mobile')),
            'password' => Hash::make($request->password),
            'memberid' => $memberCode,
            'status' => 'Active',
            'kyc_status' => 'Verified',
            'wallet' => 0,
            'p2p_wallet' => 0,
            'package' => 0,
            'left_count' => 0,
            'right_count' => 0,
        ]);

        // Establish session
        session([
            'MEMBER_ID' => $member->id,
            'MEMBER_NAME' => $member->name,
            'MEMBER_CODE' => $member->memberid,
            'IS_OWNER' => false,
            'MEMBER_ROLE' => 'user',
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Account created successfully! Redirecting...',
                'redirect' => route('member.ai.voice-assistant'),
            ]);
        }

        return redirect()->route('member.ai.voice-assistant');
    }

    /**
     * Password-only Login
     */
    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ], [
            'password.required' => 'Please enter your password to sign in.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput()->with('tab', 'login');
        }

        $password = (string) $request->input('password');
        $variants = array_unique(array_filter([
            $password,
            str_replace(' ', '', $password),
            strtolower($password),
            str_replace(' ', '', strtolower($password)),
            ucfirst($password),
        ]));

        // Find member by matching password (hashed or legacy plaintext)
        // Check active members, latest first
        $members = MemberDetail::orderBy('id', 'desc')->get();
        $matchedMember = null;

        foreach ($members as $candidate) {
            foreach ($variants as $variant) {
                if (Hash::check($variant, $candidate->password) || $candidate->password === $variant) {
                    $matchedMember = $candidate;
                    break 2;
                }
            }
        }

        if (! $matchedMember) {
            $msg = 'Incorrect password. Please enter the password you created during signup.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['password' => $msg])->withInput()->with('tab', 'login');
        }

        // Upgrade legacy unhashed password if needed
        if ($matchedMember->password === $password && ! Hash::needsRehash($matchedMember->password)) {
            $matchedMember->password = Hash::make($password);
            $matchedMember->save();
        }

        // Establish session
        session([
            'MEMBER_ID' => $matchedMember->id,
            'MEMBER_NAME' => $matchedMember->name,
            'MEMBER_CODE' => $matchedMember->memberid,
            'IS_OWNER' => (bool) ($matchedMember->is_owner || $matchedMember->role === 'owner'),
            'MEMBER_ROLE' => $matchedMember->role ?: ($matchedMember->is_owner ? 'owner' : 'user'),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged in successfully! Redirecting...',
                'redirect' => route('member.ai.voice-assistant'),
            ]);
        }

        return redirect()->route('member.ai.voice-assistant');
    }

    /**
     * Update Profile Details & Profile Image
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $memberId = session('MEMBER_ID');
        $member = MemberDetail::find($memberId);

        if (! $member) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated session. Please log in again.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:150|unique:member_details,email,'.$member->id,
            'mobile' => 'required|string|min:8|max:20|unique:member_details,mobile,'.$member->id,
            'password' => 'nullable|string|min:4|max:100',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $member->name = trim($request->string('name'));
        $member->email = trim(strtolower($request->string('email')));
        $member->mobile = trim($request->string('mobile'));

        if ($request->filled('password')) {
            $member->password = Hash::make($request->password);
        }

        // Handle Profile Image Upload
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $uploadDir = public_path('uploads/profiles');

            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            $fileName = 'profile_'.$member->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);

            $member->profile_image = 'uploads/profiles/'.$fileName;
        }

        $member->save();

        session(['MEMBER_NAME' => $member->name]);

        $avatarUrl = $member->profile_image ? asset($member->profile_image) : null;
        $avatarInitial = strtoupper(substr($member->name, 0, 1));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'mobile' => $member->mobile,
                'avatar_url' => $avatarUrl,
                'avatar_initial' => $avatarInitial,
            ],
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->flush();
        $request->session()->regenerateToken();
        $request->session()->put('LOGGED_OUT', true);

        return redirect()->route('auth.form')->with('status', 'You have been signed out.');
    }
}
