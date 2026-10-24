# IRA Voice AI — Complete Form Inventory & Field Specification

**Document Version:** 1.0.0  
**Audit Date:** October 24, 2026  
**Target Application:** `C:\xampp\htdocs\aidemo\` (Laravel 10 MLM & Staking Platform)  
**Scope:** Exhaustive catalog of every HTML `<form>` across Member, Admin, and Public interfaces  
**Mode:** Strict Read-Only Audit — No Code Altered  

---

## 1. Executive Summary & Inventory Methodology

This document provides a comprehensive technical catalog of all interactive forms in the AI Demo application. Every form was discovered through automated AST/DOM scanning, verified against Blade templates, cross-referenced with `routes/web.php`, and analyzed against its corresponding Laravel controller validation logic.

### Form Summary Statistics
- **Total Blade Views with Forms:** 32 views
- **Total Distinct HTML Forms:** 41 forms
- **Member Portal Forms:** 16 active user workflows
- **Admin Portal Forms:** 21 administrative workflows
- **Public / Authentication Forms:** 4 entry points
- **High-Risk Financial / Security Forms:** 11 forms requiring two-stage confirmation & PIN authorization

---

## 2. Complete Member Portal Form Inventory

The Member portal contains 16 active forms controlling user identity, account activation, multi-tier staking investments, internal wallet transfers, withdrawals, and customer support.

---

### Form M-01: Member Sign In
- **View Path:** `resources/views/member/signin.blade.php`
- **Form Selector:** `form[action*="userLogin"]` (No HTML ID)
- **Action Route:** `route('userLogin')` (`POST /userLogin`)
- **Controller Action:** `MemberDetailController@authenticate`
- **Purpose:** Authenticates members into their dashboard session.
- **Risk Level:** Medium (Authentication)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `memberid` | `signin-username` | Member ID | `text` | Alphanumeric (e.g. `GAB10025`) | Yes | `required` | None |
| `password` | `signin-password` | Password | `password` | Plaintext string | Yes | `required` | Toggle visibility script |
| `login-captcha` | `login-captcha` | Captcha Result | `text` | Numeric sum of two random numbers | Yes | `required` (Matches `str1 + str2`) | Math challenge generated in session |
| `_token` | *(Hidden)* | None | `hidden` | CSRF token | Yes | Valid CSRF | Blade `@csrf` |

---

### Form M-02: Member Registration / Sign Up
- **View Path:** `resources/views/member/signup.blade.php`
- **Form Selector:** `#signupForm`
- **Action Route:** `route('userRegister')` (`POST /userRegister`)
- **Controller Action:** `MemberDetailController@userregister`
- **Purpose:** Onboards new members under a sponsor.
- **Risk Level:** Medium (User Creation)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `sponsor` | `sponsor` | Sponsor ID | `text` | Existing member ID | Yes | `required\|exists:member_details,memberid` | **AJAX:** On blur/input, calls `POST /getSponname` to display sponsor name in `#sponsor_name` |
| `name` | `name` | Full Name | `text` | String | Yes | `required` | None |
| `email` | `email` | Email Address | `email` | Valid email | Yes | `required\|email` | None |
| `mobile` | `mobile` | Mobile Number | `text` | 10-digit number | Yes | `required\|min:10\|max:10` | None |
| `country` | `country` | Country | `text` | Country name | Yes | `required` | None |
| `password` | `signup-password` | Password | `password` | Min 6 chars | Yes | `required\|same:confirm_password\|min:6` | Password strength indicator |
| `confirm_password` | `confirm-password` | Confirm Password | `password` | Matching password | Yes | `required` | Password match check |
| *(checkbox)* | `defaultCheck1` | Agree Terms & Conditions | `checkbox` | Checked (`on`) | Yes | Browser `required` | Gated submit button |

---

### Form M-03: Member Forget Password
- **View Path:** `resources/views/member/forget-password.blade.php`
- **Form Selector:** `form[action*="memretrivePassword"]` (No HTML ID)
- **Action Route:** `route('memretrivePassword')` (`POST /memretrivePassword`)
- **Controller Action:** `MemberDetailController@memretrivePassword`
- **Purpose:** Sends password recovery details to registered email.
- **Risk Level:** Low

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `email` | `signin-username` | Email Address | `email` | Valid email address | Yes | `required\|email` | None |

---

### Form M-04: User Profile Summary / Edit
- **View Path:** `resources/views/member/profile/user-summary.blade.php`
- **Form Selector:** `form[action*="insertProfile"]` (No HTML ID)
- **Action Route:** `route('insertProfile')` (`POST /insertProfile`)
- **Controller Action:** `MemberDetailController@insertPdata`
- **Purpose:** Updates personal details and profile picture.
- **Risk Level:** Low

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `id` | *(Hidden)* | None | `hidden` | Numeric member record ID | Yes | Internal record lookup | Auto-populated |
| `name` | *(None)* | Name | `text` | String | No | Optional / Unrestricted | Pre-filled with `$data['name']` |
| `email` | *(None)* | Email | `text` | Read-only email | No | Readonly | Disabled/Readonly display |
| `mobile` | *(None)* | Mobile | `text` | 10-digit string | No | Optional | Pre-filled |
| `country` | *(None)* | Country | `text` | Country string | No | Optional | Pre-filled |
| `profile_image` | *(None)* | Profile Image | `file` | `image` (jpg, png, webp) | No | Optional file upload | Image preview on file change |

---

### Form M-05: Change Transaction Password
- **View Path:** `resources/views/member/profile/security.blade.php` (Form #1)
- **Form Selector:** `form[action*="txnpassChange"]`
- **Action Route:** `route('txnpassChange')` (`POST /txnpassChange`)
- **Controller Action:** `MemberDetailController@txnpassChange`
- **Purpose:** Sets or resets the member's financial transaction password.
- **Risk Level:** Critical (Security Credential)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `id` | *(Hidden)* | None | `hidden` | User ID | Yes | Internal ID | Pre-filled |
| `txn_password` | *(None)* | Transaction Password | `password` | Min 6 chars | Yes | `required\|same:txn_password_confirm\|min:6` | None |
| `txn_password_confirm` | *(None)* | Confirm Transaction Password | `password` | Min 6 chars | Yes | `min:6` | None |

---

### Form M-06: Change Account Login Password
- **View Path:** `resources/views/member/profile/security.blade.php` (Form #2)
- **Form Selector:** `form[action*="passChange"]`
- **Action Route:** `route('passChange')` (`POST /passChange`)
- **Controller Action:** `MemberDetailController@passChange`
- **Purpose:** Changes the account login password.
- **Risk Level:** Critical (Security Credential)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `id` | *(Hidden)* | None | `hidden` | User ID | Yes | Internal ID | Pre-filled |
| `password` | *(None)* | Password | `password` | Min 6 chars | Yes | `required\|same:password_confirm\|min:6` | None |
| `password_confirm` | *(None)* | Confirm Password | `password` | Min 6 chars | Yes | `min:6` | None |

---

### Form M-07: Wallet Address Submission
- **View Path:** `resources/views/member/profile/wallet-address.blade.php`
- **Form Selector:** `form[action*="insertWalletAddress"]`
- **Action Route:** `route('insertWalletAddress')` (`POST /insertWalletAddress`)
- **Controller Action:** `MemberDetailController@insertWalletAddress`
- **Purpose:** Registers member's crypto wallet address for payouts.
- **Risk Level:** High (Payout Destination)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `id` | *(Hidden)* | None | `hidden` | User record ID | Yes | Internal ID | Pre-filled |
| `wallet_address` | *(None)* | Wallet Address | `text` | Crypto address (e.g. TRC20/BEP20) | Yes | Required in HTML | Sets `kyc_status = 'Submitted'` |

---

### Form M-08: Account Activation
- **View Path:** `resources/views/member/account/account-activation.blade.php`
- **Form Selector:** `form[action*="userCreate"]` / `form[action*="activateUser"]`
- **Action Route:** Target has `route('activateUser')` in Blade; Actual route in `web.php` is `route('userCreate')` (`POST /userCreate`) *(See Anomaly Section)*
- **Controller Action:** `MemberDetailController@userCreate`
- **Purpose:** Activates member account by debiting wallet balance.
- **Risk Level:** Critical (Financial Mutation)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `memberid` | `memberid` | Member ID | `text` | Member ID string | Yes | Read-only | Pre-filled with `$data['memberid']` |
| `balance` | `basic-default-dob` | Available Balance | `text` | Currency string | Yes | Read-only | Displays current wallet balance |
| `txn_password` | *(None)* | Transaction Password | `password` | Transaction PIN/Password | Yes | `required` (Validated against hash) | Security verification |

---

### Form M-09: Upgrade Account Activation
- **View Path:** `resources/views/member/account/upgrade-activation.blade.php`
- **Form Selector:** `form[action*="userCreate"]`
- **Action Route:** `route('userCreate')` (`POST /userCreate`)
- **Controller Action:** `MemberDetailController@userCreate`
- **Purpose:** Upgrades member account tier.
- **Risk Level:** Critical (Financial Mutation)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `id` | *(Hidden)* | None | `hidden` | User ID | Yes | Internal ID | Pre-filled |
| `memberid` | `memberid` | Member ID | `text` | Member ID | Yes | Read-only | Pre-filled |
| `balance` | `basic-default-dob` | Available Balance | `text` | Currency string | Yes | Read-only | Pre-filled |
| `txn_password` | *(None)* | Transaction Password | `password` | Security PIN | Yes | `required` | Security verification |

---

### Form M-10: Import Funds / Add Funds
- **View Path:** `resources/views/member/fund/import-fund.blade.php`
- **Form Selector:** `#myForm`
- **Action Route:** `route('instantFunds')` (`POST /instantFunds`)
- **Controller Action:** `FundController@instantFunds`
- **Purpose:** Initiates an instant deposit gateway order.
- **Risk Level:** High (Deposit Request)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `memberid` | *(Hidden)* | None | `hidden` | Member ID | Yes | Read-only session ID | Pre-filled |
| `inr_amount` | `inr_amount` | INR Calculation | `hidden` | Calculated INR value | Yes | `required` | Auto-computed: `amount * exchange_rate` |
| `amount` | `amount` | Enter Amount | `number` | Minimum $100 | Yes | `required\|min:0` (Enforces >= 100 in PHP) | Dynamic conversion calculation on input |
| `security_pin` | `security_pin` | Security PIN | `password` | 6-char transaction password | Yes | `required` (Hash check against member) | Security verification |

---

### Form M-11: P2P Wallet Transfer (Member-to-Member)
- **View Path:** `resources/views/member/p2p/p2p-wallet.blade.php`
- **Form Selector:** `form[action*="fundTrans"]` (No HTML ID)
- **Action Route:** `route('fundTrans')` (`POST /fundTrans`)
- **Controller Action:** `FundController@fundTrans`
- **Purpose:** Transfers funds from sender to another member's wallet.
- **Risk Level:** Critical (Direct Financial Debit)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `memberid` | `fundmemberid` | Recipient Member ID | `text` | Valid Member ID | Yes | `required` | **AJAX:** On blur/input, calls `POST /getMember` to fetch recipient's full name |
| `amount` | `transfer_amount` | Transfer Amount | `text` | Positive number | Yes | `required\|numeric` | Balance check against `sender->wallet` |
| `txn_password` | `inptxnpass` | Transaction Password | `password` | Security password | Yes | `required` | Hash verified before transfer |
| `balance` | `balance` | Available Balance | `hidden` | Float | Yes | Hidden reference | Pre-filled |
| `sender` | *(Hidden)* | Sender ID | `hidden` | Member ID | Yes | Hidden reference | Pre-filled |

---

### Form M-12: Wallet to P2P Transfer (Self Internal)
- **View Path:** `resources/views/member/wallet/wallet-to-p2p.blade.php`
- **Form Selector:** `form[action*="walletP2pTransfer"]` (No HTML ID)
- **Action Route:** `route('walletP2pTransfer')` (`POST /walletP2pTransfer`)
- **Controller Action:** `FundController@walletP2pTransfer`
- **Purpose:** Transfers balance from Main Wallet to P2P Fund Wallet (5% fee).
- **Risk Level:** Critical (Financial Balance Move)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `amount` | `transfer_amount` | Transfer Amount | `text` | Positive float | Yes | `required\|numeric\|min:1` | Calculates 5% fee dynamically; checks wallet balance |
| `pin` | *(None)* | Security PIN | `password` | Transaction PIN | Yes | `required` | Hash checked against `txn_password` |
| `memberid` | *(Hidden)* | Member ID | `hidden` | Active member ID | Yes | Self transfer | Pre-filled |

---

### Form M-13: Main Wallet Withdrawal Request
- **View Path:** `resources/views/member/wallet/main-wallet.blade.php`
- **Form Selector:** `form[action*="initWithdrawal"]` (No HTML ID)
- **Action Route:** `route('initWithdrawal')` (`POST /initWithdrawal`)
- **Controller Action:** `WithdrawalController@initWithdrawal`
- **Purpose:** Submits an external payout request to registered crypto address.
- **Risk Level:** Critical (Financial Payout Request)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `amount` | `amount` | Withdrawal Amount | `text` | Minimum $10 | Yes | `required\|numeric` (Enforces >= $10) | Validates against wallet balance |
| `txn_password` | *(None)* | Transaction Password | `password` | Security password | Yes | `required` | Verified via `Hash::check` |
| `memberid` | `memberid` | Member ID | `hidden` | Current user ID | Yes | Internal session ID | Pre-filled |
| `requestId` | `requestId` | Request Nonce | `hidden` | Unique random string | Yes | Prevents double submission | Auto-generated |

---

### Form M-14: Investment / Staking Package
- **View Path:** `resources/views/member/staking/staking.blade.php`
- **Form Selector:** `#stakingForm`
- **Action Route:** `route('createStaking')` (`POST /createStaking`)
- **Controller Action:** `StakingController@createStaking`
- **Purpose:** Locks funds into a daily ROI staking contract.
- **Risk Level:** Critical (High-Value Investment Contract)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `memberid` | `memberid` | Member ID | `text` | Member ID | Yes | Read-only | Pre-filled |
| `stak_package` | `stak_package` | Staking Package | `select` | `Silver` ($100-$1000), `Gold` ($1100-$5000), `Platinum` ($5100-$10000), `Diamond` ($10100+) | Yes | `required` | **JS Cascade:** Dynamically updates min/max limits on `#amount` field and displays package benefits |
| `amount` | `amount` | Investment Amount | `number` | Numeric within package limits | Yes | `required\|numeric` | Validated by client JS against package boundaries and wallet balance |
| `txn_password` | *(None)* | Transaction Password | `password` | Security password | Yes | `required` | Hash verified |

---

### Form M-15: Create Support Ticket
- **View Path:** `resources/views/member/support/create-ticket.blade.php`
- **Form Selector:** `form[action*="createTicket"]`
- **Action Route:** `route('createTicket')` (`POST /createTicket`)
- **Controller Action:** `SupportController@createTicket`
- **Purpose:** Submits an inquiry or technical issue to administrators.
- **Risk Level:** Low

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `memberid` | `memberid` | Member ID | `text` | Member ID | Yes | Read-only | Pre-filled |
| `subject` | `subject` | Subject | `text` | String | Yes | `required` | None |
| `issue` | *(None)* | Message | `textarea` | Problem description | Yes | `required` | None |

---

### Form M-16: Reply to Support Ticket
- **View Path:** `resources/views/member/support/view-support-tickets.blade.php`
- **Form Selector:** `form[action*="MbReplyTicket"]`
- **Action Route:** `route('MbReplyTicket')` (`POST /MbReplyTicket`)
- **Controller Action:** `SupportController@MbReplyTicket`
- **Purpose:** Appends a message to an active ticket thread.
- **Risk Level:** Low

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `ticketid` | *(Hidden)* | Ticket ID | `hidden` | Numeric ID | Yes | Internal ID | Pre-filled |
| `reply` | *(None)* | Enter Your Reply | `textarea` | Reply text | Yes | `required` | None |

---

## 3. Complete Admin Portal Form Inventory

The Admin portal contains 21 distinct administrative forms for managing platform solvency, manual fund adjustments, withdrawal authorizations, security overrides, broadcast messages, and exchange rates.

---

### Form A-01: Admin Login
- **View Path:** `resources/views/admin/login.blade.php`
- **Form Selector:** `form[action*="adminLogin"]`
- **Action Route:** `route('adminLogin')` (`POST /adminLogin`)
- **Controller Action:** `AdminController@authenticate`
- **Risk Level:** Critical (Admin Master Access)

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|
| `email` | `email` | Email Address | `email` | Yes | `required\|email` | None |
| `password` | `password` | Password | `password` | Yes | `required` | None |
| `otp` | *(None)* | OTP (Conditional) | `text` | No | Optional | Displayed if 2FA enabled |

---

### Form A-02: Admin Forget Password
- **View Path:** `resources/views/admin/forget-password.blade.php`
- **Form Selector:** `form[action*="retrivePassword"]`
- **Action Route:** `route('retrivePassword')` (`POST /retrivePassword`)
- **Controller Action:** `AdminController@retrivePassword`
- **Risk Level:** High (Password Reset Trigger)
- **Fields:** None. Form consists solely of a `@csrf` token and a single `<button type="submit">Retrive Password</button>`. Dispatches reset details to hardcoded administrator email.

---

### Form A-03: Admin Profile Email Update
- **View Path:** `resources/views/admin/profile.blade.php` (Form #1)
- **Form Selector:** `form[action*="changeEmail"]`
- **Action Route:** `route('changeEmail')` (`POST /changeEmail`)
- **Controller Action:** `AdminController@changeEmail`
- **Risk Level:** Critical (Administrative Recovery Email)

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|
| `email` | `exampleInputName1` | Enter New Email | `email` | Yes | `required\|email` | Pre-filled with `$admin['email']` |

---

### Form A-04: Admin Profile Password Update
- **View Path:** `resources/views/admin/profile.blade.php` (Form #2)
- **Form Selector:** `form[action*="changePassword"]`
- **Action Route:** `route('changePassword')` (`POST /changePassword`)
- **Controller Action:** `AdminController@change`
- **Risk Level:** Critical (Admin Credential)

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|
| `password` | `exampleInputName1` | Enter Password | `password` | Yes | `required\|same:password_confirmation\|min:6` | None |
| `password_confirmation` | `exampleInputName1` | Retype Password | `password` | Yes | `required` | None |

---

### Form A-05: Add Member Funds (Admin Credit)
- **View Path:** `resources/views/admin/funds/add-funds.blade.php`
- **Form Selector:** `form[action*="addMemFunds"]`
- **Action Route:** `route('addMemFunds')` (`POST /addMemFunds`)
- **Controller Action:** `AdminController@addMemFunds`
- **Purpose:** Manually credits money to any member's wallet.
- **Risk Level:** Critical (Unbacked Financial Minting)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `memberid` | `memberid` | Member ID | `text` | Target Member ID | Yes | `required\|exists:member_details,memberid` | **AJAX:** Calls `POST /getMember` on blur to display member name and current balances |
| `wallet_type` | *(None)* | Select Wallet Type | `select` | `Wallet` (Main), `Fund Wallet` (P2P) | Yes | Required in HTML | Determines credited balance column |
| `amount` | *(None)* | Amount ($) | `text` | Numeric >= 0 | Yes | `required\|min:0` | None |
| `txnid` | *(None)* | Transaction ID | `text` | String reference | No | Optional | None |

---

### Form A-06: Deduct Member Funds (Admin Debit)
- **View Path:** `resources/views/admin/funds/deduct-funds.blade.php`
- **Form Selector:** `form[action*="deductMemFunds"]`
- **Action Route:** `route('deductMemFunds')` (`POST /deductMemFunds`)
- **Controller Action:** `AdminController@deductMemFunds`
- **Purpose:** Manually debits money from any member's wallet.
- **Risk Level:** Critical (Direct Balance Confiscation)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `memberid` | `memberid` | Member ID | `text` | Target Member ID | Yes | `required` | **AJAX:** Calls `POST /getMember` to fetch balances |
| `wallet_type` | *(None)* | Select Wallet Type | `select` | `Wallet` (Main), `Fund Wallet` (P2P) | Yes | Required in HTML | Target wallet |
| `amount` | *(None)* | Amount ($) | `text` | Numeric >= 0 | Yes | `required\|min:0` | Verified against member balance |

---

### Form A-07: Free Pin Account Activation
- **View Path:** `resources/views/admin/members/free-pin-account.blade.php`
- **Form Selector:** `form[action*="PinUserAccount"]`
- **Action Route:** `route('PinUserAccount')` (`POST /PinUserAccount`)
- **Controller Action:** `AdminController@PinUserAccount`
- **Purpose:** Administratively activates an account without wallet deduction.
- **Risk Level:** High

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `memberid` | *(Hidden)* | Member ID | `hidden` | Target Member ID | Yes | Pre-filled | None |
| `package` | `package` | Select Package | `select` | `Silver`, `Gold`, `Platinum`, `Diamond` | Yes | `required` | None |
| `amount` | `amount` | Package Amount | `text` | Numeric dollar value | Yes | Required in HTML | Value must fit package range |

---

### Form A-08: Member Profile Full Edit
- **View Path:** `resources/views/admin/members/member-update.blade.php`
- **Form Selector:** `form[action*="updateDetails"]`
- **Action Route:** `route('updateDetails')` (`POST /updateDetails`)
- **Controller Action:** `AdminController@updateDetails`
- **Risk Level:** High (Identity & Password Override)

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| `id` | *(Hidden)* | Record ID | `hidden` | Yes | Database primary key |
| `name` | *(None)* | Name | `text` | No | Optional string |
| `email` | *(None)* | Email | `text` | Yes | `required\|email` |
| `mobile` | *(None)* | Mobile | `text` | No | Optional string |
| `father` | *(None)* | Father Name | `text` | No | Optional string |
| `dob` | *(None)* | Date of Birth | `date` | No | Optional date |
| `address` | *(None)* | Address | `text` | No | Optional string |
| `city` | *(None)* | City | `text` | No | Optional string |
| `state` | *(None)* | State | `text` | No | Optional string |
| `country` | *(None)* | Country | `text` | No | Optional string |
| `gender` | *(None)* | Gender | `text` | No | Optional string |
| `occupation` | *(None)* | Occupation | `text` | No | Optional string |
| `password` | *(None)* | New Password | `password` | No | Plaintext update |
| `txn_password` | *(None)* | New Txn Password | `password` | No | Plaintext update |
| `image` | *(None)* | Profile Image | `file` | No | Optional file upload |

---

### Form A-09: Member Security Override
- **View Path:** `resources/views/admin/member-security.blade.php`
- **Form Selector:** `form[action*="securityUpdate"]`
- **Action Route:** `route('securityUpdate')` (`POST /securityUpdate`)
- **Controller Action:** `AdminController@securityUpdate`
- **Risk Level:** High (Direct Password Reset)

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| `id` | *(Hidden)* | Member ID Record | `hidden` | Yes | Database ID |
| `password` | *(None)* | Enter Password | `text` | No | Plaintext (Admin can assign readable string) |
| `txnpassword` | *(None)* | Enter Txn Password | `text` | No | Plaintext update |

---

### Form A-10: Dashboard Broadcast Message
- **View Path:** `resources/views/admin/dash-messages.blade.php`
- **Form Selector:** `form[action*="dashMsg"]`
- **Action Route:** `route('dashMsg')` (`POST /dashMsg`)
- **Controller Action:** `AdminController@dashMsg`
- **Risk Level:** Low

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| `rank` | *(None)* | Enter Rank | `number` | Yes | `required\|unique:dash_messages,rank` |
| `message` | *(None)* | Enter Message | `text` | Yes | `required` |

---

### Form A-11: Achievers Image Upload
- **View Path:** `resources/views/admin/achievers-image.blade.php`
- **Form Selector:** `form[action*="acheivImage"]`
- **Action Route:** `route('acheivImage')` (`POST /acheivImage`)
- **Controller Action:** `AdminController@acheivImage`
- **Risk Level:** Low

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| `rank` | *(None)* | Image Rank | `number` | Yes | `required` |
| `title` | *(None)* | Title | `text` | Yes | `required` |
| `image` | *(None)* | Upload Image | `file` | Yes | `required\|image` |

---

### Form A-12: Popup Image Announcement
- **View Path:** `resources/views/admin/popup-image.blade.php`
- **Form Selector:** `form[action*="admin.popupImageUpdate"]`
- **Action Route:** `route('admin.popupImageUpdate')` (`POST /popup-image/update`)
- **Controller Action:** `AdminController@popupImageUpdate`
- **Risk Level:** Low

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| `title` | *(None)* | Announcement Title | `text` | No | `nullable\|string\|max:255` |
| `link` | *(None)* | Destination URL | `url` | No | `nullable\|string\|max:500` |
| `status` | `statusActive` / `statusInactive` | Status | `radio` | No | `nullable\|in:0,1` |
| `image` | `popupImageInput` | Popup Banner Image | `file` | No | `nullable\|image\|mimes:jpeg,png,jpg,webp,gif\|max:5120` |

---

### Form A-13: Set Exchange Rates
- **View Path:** `resources/views/admin/set-rates.blade.php`
- **Form Selector:** `form[action*="rateUpdate"]`
- **Action Route:** `route('rateUpdate')` (`POST /rateUpdate`)
- **Controller Action:** `AdminController@rateUpdate`
- **Purpose:** Configures global USD to INR conversion rate.
- **Risk Level:** High (Affects all Deposit Calculations)

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| `rate` | *(None)* | Dollar Rate (INR) | `text` | Yes | `required` |

---

### Form A-14: Admin Deposit Receiving Wallet
- **View Path:** `resources/views/admin/wallet-address.blade.php`
- **Form Selector:** `form[action*="updateWallet"]`
- **Action Route:** `route('updateWallet')` (`POST /updateWallet`)
- **Controller Action:** `AdminController@updateWallet`
- **Risk Level:** Critical (Platform Inbound Funds Target)

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| `id` | *(Hidden)* | ID | `hidden` | Yes | Pre-filled |
| `address` | *(None)* | Company USDT Address | `text` | Yes | `required` |

---

### Form A-15: Deposit Bank & UPI Details
- **View Path:** `resources/views/admin/deposit/deposit-details.blade.php` (Form #1)
- **Form Selector:** `form[action*="depositData"]`
- **Action Route:** `POST /depositData`
- **Controller Action:** `AdminController@depositData`
- **Risk Level:** High

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| `bank` | *(None)* | Bank Name | `text` | Yes | `required` |
| `ifsc` | *(None)* | IFSC Code | `text` | Yes | `required` |
| `account_no` | *(None)* | Account No | `text` | Yes | `required` |
| `upi` | *(None)* | UPI ID | `text` | Yes | `required` |
| `qrcode` | *(None)* | QR Code | `file` | No | Optional image file |

---

### Form A-16: Deposit USDT Wallet Details
- **View Path:** `resources/views/admin/deposit/deposit-details.blade.php` (Form #2)
- **Form Selector:** `form[action*="depositDataUSDT"]`
- **Action Route:** `POST /depositDataUSDT`
- **Controller Action:** `AdminController@depositDataUSDT`
- **Risk Level:** High

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| `wallet` | *(None)* | Wallet Address | `text` | Yes | `required` |
| `uqrcode` | *(None)* | QR Code Image | `file` | Yes | `required\|image:jpg,jpeg,png` |

---

### Form A-17: Withdrawal Accept with Transaction Hash
- **View Path:** `resources/views/admin/withdrawal-comment.blade.php` (Form #1)
- **Form Selector:** `form[action*="withdrawal.accept"]`
- **Action Route:** `route('withdrawal.accept', $data['id'])` (`POST /withdrawal/accept/{id}`)
- **Controller Action:** `AdminController@wAccept`
- **Purpose:** Marks a withdrawal request as paid and attaches blockchain hash.
- **Risk Level:** Critical (Financial Settlement)

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| *(memberid)* | *(None)* | Member ID | `text` | No | Read-only display |
| `transaction_hash` | *(None)* | Transaction Hash | `text` | Yes | `required` |
| `comment` | *(None)* | Comment | `textarea` | No | `nullable\|string` |

---

### Form A-18: Withdrawal Reject with Reason
- **View Path:** `resources/views/admin/withdrawal-comment.blade.php` (Form #2)
- **Form Selector:** `form[action*="withdrawal.cancel"]`
- **Action Route:** `route('withdrawal.cancel', $data['id'])` (`POST /withdrawal/cancel/{id}`)
- **Controller Action:** `AdminController@wCancel`
- **Purpose:** Rejects a withdrawal request and refunds balance to member.
- **Risk Level:** Critical (Financial Cancellation)

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| *(memberid)* | *(None)* | Member ID | `text` | No | Read-only display |
| `comment` | *(None)* | Comment / Rejection Reason | `textarea` | No | `nullable\|string` |

---

### Form A-19: Withdrawal Direct Online Pay Request
- **View Path:** `resources/views/admin/withdrawal-accept.blade.php`
- **Form Selector:** `form[action*="sendWdlRequest"]`
- **Action Route:** `route('sendWdlRequest')` (`POST /sendWdlRequest`)
- **Controller Action:** `WithdrawalController@sendWdlRequest`
- **Purpose:** Triggers automated blockchain API payout.
- **Risk Level:** Critical (Automated Fund Outflow)

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| `memberid` | *(None)* | Member ID | `text` | Yes | Read-only |
| `wallet` | *(None)* | Wallet Address | `text` | Yes | Read-only |
| `net_amount` | *(None)* | Net Amount ($) | `text` | Yes | Read-only |
| `wid` | *(Hidden)* | Withdrawal ID | `hidden` | Yes | Numeric record ID |

---

### Form A-20: History Advanced Date Filter
- **View Path:** `resources/views/admin/partials/history-date-filter.blade.php`
- **Form Selector:** `#advancedDateFilterForm`
- **Action Route:** `$currentBaseUrl` (`GET`)
- **Purpose:** Filters report tables by pre-set or custom date intervals.
- **Risk Level:** Low (Read-Only Filter)

| Field Name | HTML ID | Visible Label | Input Type | Allowed Values / Options | Req | Validation Rules | Dynamic Behaviors |
|---|---|---|---|---|---|---|---|
| `filter_mode` | *(Hidden)* | None | `hidden` | `advanced` | Yes | Filter mode flag | Hidden |
| `preset` | `adv_preset` | Preset | `select` | `today`, `yesterday`, `last_7_days`, `last_30_days`, `this_month`, `last_month`, `this_year`, `custom` | No | Optional string | **JS Cascade:** Auto-computes and sets `from_date` and `to_date` values on preset change |
| `from_date` | `adv_from_date` | From Date | `date` | YYYY-MM-DD | Yes | Date string | Populated by preset |
| `to_date` | `adv_to_date` | To Date | `date` | YYYY-MM-DD | Yes | Date string | Populated by preset |

---

### Form A-21: Admin Support Ticket Reply
- **View Path:** `resources/views/admin/support/view-support-ticket.blade.php`
- **Form Selector:** `form[action*="Ticket"]`
- **Action Route:** `route('Ticket')` (`POST /Ticket`)
- **Controller Action:** `SupportController@Ticket`
- **Risk Level:** Low

| Field Name | HTML ID | Visible Label | Input Type | Req | Validation Rules |
|---|---|---|---|---|---|
| `ticket_id` | *(Hidden)* | Ticket ID | `hidden` | Yes | Pre-filled |
| `reply` | *(None)* | Type and enter | `text` | Yes | `required` |

---

## 4. Dynamic Behaviors & JavaScript Dependencies

Voice form automation cannot simply inject values into HTML inputs; it must respect and trigger client-side scripts attached to those inputs:

### 1. Dynamic AJAX Member & Sponsor Lookups
- **Endpoint:** `POST /getSponname` (Used in `member/signup.blade.php`)
  - **Trigger:** Changing `#sponsor`
  - **Behavior:** Fetches sponsor name from DB. If valid, populates `#sponsor_name` and turns border green. If invalid, shows *"Invalid Sponsor ID"* error.
- **Endpoint:** `POST /getMember` (Used in `p2p-wallet.blade.php`, `add-funds.blade.php`, `deduct-funds.blade.php`)
  - **Trigger:** Changing `#fundmemberid` or `#memberid`
  - **Behavior:** Queries member record and dynamically renders full name, active status, and current wallet balance in an adjacent badge.
- **Voice Assistant Requirement:** After filling a Member or Sponsor ID by voice, the assistant must dispatch `input` and `change` events so the AJAX handler fires. If the lookup returns an invalid user, the assistant must speak: *"Yeh Member ID uplabdh nahi hai."*

### 2. Client-Side Package Min/Max Enforcement
- **Form:** `member/staking/staking.blade.php` (`#stakingForm`)
- **Behavior:** When `#stak_package` changes:
  - `Silver`: Sets `#amount` min to 100, max to 1000.
  - `Gold`: Sets `#amount` min to 1100, max to 5000.
  - `Platinum`: Sets `#amount` min to 5100, max to 10000.
  - `Diamond`: Sets `#amount` min to 10100.
- **Voice Assistant Requirement:** The assistant must validate the spoken amount against the chosen package before setting the input, avoiding client-side form validation blocks.

### 3. Date Preset Auto-Calculations
- **Form:** `admin/partials/history-date-filter.blade.php` (`#advancedDateFilterForm`)
- **Behavior:** Selecting `#adv_preset` automatically writes calculated date strings into `#adv_from_date` and `#adv_to_date`.
- **Voice Assistant Requirement:** Setting `#adv_preset` by voice and triggering `change` will naturally populate the date inputs without needing separate date calculations.

---

## 5. Risk & Transactional Classification Matrix

Every form is assigned a safety classification determining whether voice can fill and/or submit the form:

```
+-----------------------------------------------------------------------------------------+
|                               RISK CLASSIFICATION TIERS                                 |
+-------------------+-----------------------------------+---------------------------------+
| TIER              | CRITERIA                          | VOICE BEHAVIOR POLICY           |
+-------------------+-----------------------------------+---------------------------------+
| 1. CRITICAL       | Direct financial debit/credit,    | - Full multi-turn slot filling  |
|    (Financial /   | withdrawals, staking contracts,   | - Visual field highlighting     |
|     Security)     | password & PIN modifications.     | - MUST summarize all values     |
|                   |                                   | - MUST require spoken confirm   |
|                   |                                   | - MUST verify txn_password/PIN  |
|                   |                                   | - NEVER bypass credentials      |
+-------------------+-----------------------------------+---------------------------------+
| 2. MEDIUM         | Profile edits, support tickets,   | - Voice filling supported       |
|    (Operational)  | public registration.              | - Simple affirmative confirm    |
+-------------------+-----------------------------------+---------------------------------+
| 3. LOW            | Search filters, date ranges,      | - Immediate execution allowed   |
|    (Read-Only)    | genealogy lookups.                | - No confirmation required      |
+-------------------+-----------------------------------+---------------------------------+
| 4. EXCLUDED       | Forms with mandatory file/image   | - Voice explains file upload    |
|    (Multipart /   | uploads (QR codes, achievers).    |   must be chosen via browser    |
+-------------------+-----------------------------------+---------------------------------+
```

### Detailed Breakdown by Form:
- **Tier 1 (Critical):**
  - M-05 (Change Txn Password), M-06 (Change Login Password), M-07 (Wallet Address), M-08 (Account Activation), M-09 (Upgrade Activation), M-10 (Add Funds), M-11 (P2P Transfer), M-12 (Wallet to P2P), M-13 (Withdrawal), M-14 (Staking).
  - A-01 (Admin Login), A-03 (Admin Email), A-04 (Admin Password), A-05 (Admin Add Funds), A-06 (Admin Deduct Funds), A-07 (Free Pin Activation), A-08 (Member Update), A-09 (Security Override), A-14 (Admin Wallet), A-17 (Withdrawal Accept), A-18 (Withdrawal Reject), A-19 (Direct Payout).
- **Tier 2 (Medium):**
  - M-02 (Registration), M-04 (User Profile), M-15 (Create Ticket), M-16 (Reply Ticket).
  - A-10 (Dashboard Message), A-13 (Set Rates), A-21 (Admin Ticket Reply).
- **Tier 3 (Low):**
  - A-20 (History Date Filter), Geneology Search (`member/team/geneology.blade.php`).
- **Tier 4 (Excluded / Voice Partial):**
  - A-11 (Achievers Image Upload), A-12 (Popup Image), A-15 & A-16 (Deposit Details QR upload).

---

## 6. Technical Feasibility Assessment & Codebase Anomalies

During the code audit, specific implementation anomalies and constraints were identified that must be accounted for:

### Anomaly 1: Missing Route in Account Activation Blade
- **Location:** `resources/views/member/account/account-activation.blade.php` (Line 61)
- **Problem:** The Blade template has `<form action="{{ route('activateUser') }}" method="post">`.
- **Root Cause:** In `routes/web.php`, the route is named `userCreate` (`Route::post('userCreate', ...)->name('userCreate')`), not `activateUser`.
- **Runtime Impact:** Calling `route('activateUser')` throws an `InvalidArgumentException: Route [activateUser] not defined` if an unactivated user views this template.
- **Voice Assistant Impact:** The voice assistant must recognize this route alias and ensure its form mapping uses `route('userCreate')`.

### Anomaly 2: Missing HTML `id` and Duplicate Identifiers
- In views such as `admin/profile.blade.php`, multiple inputs share the identical ID `exampleInputName1`.
- In `member/profile/security.blade.php`, password inputs have **no `id` attribute**, only `name="password"` and `name="txn_password"`.
- **Voice Assistant Requirement:** The assistant's DOM updater must query elements using **`form selector + [name="fieldName"]`**, rather than relying purely on `#elementId`.

### Anomaly 3: Captcha on Member Login Form
- In `member/signin.blade.php`, login is protected by a session-backed math challenge (`$str1 + $str2 = $captcha`).
- **Voice Assistant Policy:** Voice login should not attempt to secretly solve or bypass captcha. The user can speak the sum, or the assistant can say: *"Kripya screen par captcha bhar kar login karein."*

### Anomaly 4: File Upload Fields Cannot Be Transmitted by Voice
- Inputs with `type="file"` (e.g. Profile Image in M-04, QR Codes in A-15/A-16) cannot be populated via voice recognition.
- **Voice Assistant Policy:** For file inputs, the assistant will inform the user: *"Main baaki details bhar rahi hoon, kripya file screen se select karein."*

---
*End of Deliverable 2 (Form Inventory & Field Specification)*
