# Complete Verified Navigation Inventory — AI Demo (Quantum ARB)
**Document Path:** `C:\xampp\htdocs\aidemo\docs\ira-navigation-inventory.md`  
**Target Project:** `C:\xampp\htdocs\aidemo`  
**Framework:** Laravel 10.48.29 / PHP 8.2.12  
**Source Files Audited:**
- `resources/views/member/layouts/header.blade.php` (Lines 884–1430: Member Sidebar)
- `resources/views/admin/include/sidebar.blade.php` (Lines 1–154: Admin Sidebar)
- `routes/web.php` (All registered routes, HTTP methods, controller bindings, and middleware)
- `app/Http/Controllers/MemberController.php`, `IncomeController.php`, `FundController.php`, `StakingController.php`, `LevelsController.php`, `WithdrawalController.php`, `SupportController.php`, `MemberDetailController.php`
- `app/Http/Controllers/AdminController.php`
- `app/Http/Middleware/MemberAuth.php`, `app/Http/Middleware/AdminAuth.php`

---

## 1. Member Portal Navigation Inventory

All Member destinations are guarded by the `MemberAuth` middleware (requires active session key `MEMBER_ID`). Unauthorized access redirects to `/member` (member sign-in).

| Parent Section / Category | Exact Visible Sidebar Label | Parent Type | Child Page Visible Label | Destination Key (`destination_key`) | Verified Target URL | Route / Controller Method | Authorized Roles | Spoken Aliases (EN / Hinglish / HI) | Navigable Page? |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Dashboard** | `Dashboard` | Direct Page | — *(Single Landing)* | `member_dashboard` | `/member/dashboard` | GET `[MemberController::class, 'dashboard']` | `member` | *Dashboard, Main Dashboard, Member Dashboard, Home Dashboard, डैश्बोर्ड* | Yes |
| **Profile** | `Profile` | Submenu (`has-sub`) | `Edit Profile` | `member_profile_summary` | `/member/profile/user-summary` | GET `[MemberDetailController::class, 'userSummary']` | `member` | *Edit Profile, User Summary, My Profile, Meri Profile, Profile details* | Yes |
| | | | `Change Password` | `member_profile_security` | `/member/profile/security` | GET `[MemberDetailController::class, 'security']` | `member` | *Change Password, Password Change, Security Settings, Password Badlo* | Yes |
| | | | `Settings (Wallet Address)` | `member_profile_wallet` | `/member/profile/wallet-address` | GET `[MemberDetailController::class, 'walletAddress']` | `member` | *Wallet Address Settings, My Wallet Address, Settings Wallet Address* | Yes |
| **Add Fund** | `Add Fund` | Submenu (`has-sub`) | `Add Fund` | `member_fund_import` | `/member/fund/import-fund` | GET `[FundController::class, 'importFund']` | `member` | *Add Fund, Import Fund, Deposit Fund, Deposit Money, Fund Add Karo* | Yes |
| | | | `Add Fund History` | `member_fund_history` | `/member/fund/import-fund-history` | GET `[FundController::class, 'importFundHistory']` | `member` | *Add Fund History, Import Fund History, Deposit History, Fund History* | Yes |
| **Investment** | `Investment` | Submenu (`has-sub`) | `Create Investment` | `member_staking` | `/member/staking` | GET `[StakingController::class, 'staking']` | `member` | *Create Investment, New Investment, Investment Portal, Staking Portal, Invest Karo* | Yes |
| | | | `Investment Details` | `member_staking_details` | `/member/staking/staking-details` | GET `[StakingController::class, 'stakingDetails']` | `member` | *Investment Details, Staking Details, My Investments, Investment Record* | Yes |
| **Earnings** | `Earnings` | Submenu (`has-sub`) | `Staking Income` | `member_income_staking` | `/member/income/staking-incomes` | GET `[IncomeController::class, 'stakingIncomes']` | `member` | *Staking Income, Stake Income, ROI Income, Staking Earnings* | Yes |
| | | | `Level Income` | `member_income_level` | `/member/income/level-incomes` | GET `[IncomeController::class, 'levelIncomes']` | `member` | *Level Income, Levels Income, Team Income, Level Earnings* | Yes |
| | | | `Direct Income` | `member_income_direct` | `/member/income/direct-incomes` | GET `[IncomeController::class, 'directIncomes']` | `member` | *Direct Income, Referral Income, Direct Bonus, Sponsor Income* | Yes |
| **Reward Section** | `Reward Section` | Submenu (`has-sub`) | `Reward Income` | `member_income_reward` | `/member/income/reward-incomes` | GET `[IncomeController::class, 'rewardIncomes']` | `member` | *Reward Income, Rewards Income, Inam Income, Reward Kamai* | Yes |
| | | | `Reward Details` | `member_income_reward_details` | `/member/income/reward-details` | GET `[IncomeController::class, 'rewardDetails']` | `member` | *Reward Details, Rewards Details, Rewards Status, Career Rewards* | Yes |
| **Team Management** | `Team Management` | Submenu (`has-sub`) | `Geneology` | `member_team_geneology` | `/member/team/geneology` | GET `[LevelsController::class, 'geneology']` | `member` | *Geneology, Genealogy, Team Tree, Network Tree, Downline Tree, ट्री* | Yes |
| | | | `Direct Members` | `member_team_direct` | `/member/levels/direct-members` | GET `[LevelsController::class, 'directMembers']` | `member` | *Direct Members, Direct Team, My Directs, Direct Sponsor List* | Yes |
| | | | `Level Member Table` | `member_team_level_table` | `/member/levels/level-members-table` | GET `[LevelsController::class, 'lelMemTable']` | `member` | *Level Member Table, Level Members, Level Table, All Level Team* | Yes |
| **Withdrawal Section** | `Withdrawal Section` | Submenu (`has-sub`) | `Withdrawal Fund` | `member_wallet_main` | `/member/wallet` | GET `[WithdrawalController::class, 'mainWallets']` | `member` | *Withdrawal Fund, Withdraw Fund, Main Wallet, Nikasi Fund, Paisa Nikalo* | Yes |
| | | | `Withdrawal History` | `member_wallet_history` | `/member/wallet/wallet-history` | GET `[WithdrawalController::class, 'withdrlHistory']` | `member` | *Withdrawal History, Nikasi History, Past Withdrawals, Withdrawal Records* | Yes |
| | | | `Wallet to P2P Transfer` | `member_wallet_to_p2p` | `/member/wallet/wallet-to-p2p` | GET `[FundController::class, 'walletToP2P']` | `member` | *Wallet to P2P Transfer, Wallet to P2P, Transfer to P2P* | Yes |
| | | | `Transaction History` | `member_wallet_transactions` | `/member/wallet/all-transaction` | GET `[WithdrawalController::class, 'walletTransfer']` | `member` | *Transaction History, All Transactions, Wallet Ledger, Transactions Report* | Yes |
| **P2P** | `P2P` | Submenu (`has-sub`) | `P2P Wallet` | `member_p2p_wallet` | `/member/p2p-wallet` | GET `[FundController::class, 'p2pwallet']` | `member` | *P2P Wallet, P to P Wallet, Petrol Wallet (phonetic), Peer to Peer Wallet* | Yes |
| | | | `P2P History` | `member_p2p_history` | `/member/p2p-wallet-history` | GET `[FundController::class, 'p2pHistory']` | `member` | *P2P History, P to P History, Petrol History (phonetic), P2P Transfers* | Yes |
| **Support** | `Support` | Submenu (`has-sub`) | `Create New Ticket` | `member_support_create` | `/member/support/create-ticket` | GET `[SupportController::class, 'createTick']` | `member` | *Create New Ticket, Raise Ticket, New Support Ticket, Naya Ticket* | Yes |
| | | | `Support Tickets` | `member_support_tickets` | `/member/support/support-tickets` | GET `[SupportController::class, 'supportTickets']` | `member` | *Support Tickets, My Tickets, Ticket History, Helpdesk Tickets* | Yes |
| **Utilities** | `Voice Assistant` | Modal Trigger | — | `voice_assistant_toggle` | `#iraModal` | JS Modal Trigger | `member`, `admin`, `guest` | *Voice Assistant, Voice AI, Assistant, IRA, माइक, असिस्टेंट* | UI Action |
| **Utilities** | `Logout` | Action | — | `member_logout` | `/member/logout` | GET `[MemberController::class, 'logout']` | `member` | *Logout, Sign Out, Log Out, Bahar Niklo, लॉगआउट* | Action / Nav |

---

## 2. Admin Portal Navigation Inventory

All Admin destinations are guarded by the `AdminAuth` middleware (requires active session key `ADMIN_LOGIN` === true). Unauthorized access redirects to `/admin` (admin sign-in).

| Parent Section / Category | Exact Visible Sidebar Label | Parent Type | Child Page Visible Label | Destination Key (`destination_key`) | Verified Target URL | Route / Controller Method | Authorized Roles | Spoken Aliases (EN / Hinglish / HI) | Navigable Page? |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Dashboard** | `Dashboard` | Direct Page | — *(Single Landing)* | `admin_dashboard` | `/admin/dashboard` | GET `[AdminController::class, 'dashboard']` | `admin` | *Admin Dashboard, Administrator Dashboard, Main Admin Dashboard* | Yes |
| **Popup / Dashboard Image** | `Popup / Dashboard Image` | Direct Page | — *(Single Landing)* | `admin_popup_image` | `/admin/popup-image` | GET `[AdminController::class, 'popupImage']` | `admin` | *Popup Image, Dashboard Image, Popup / Dashboard Image, Achievers Images* | Yes |
| **Dashboard Messages** | `Dashboard Messages` | Direct Page | — *(Single Landing)* | `admin_dash_messages` | `/admin/dash-messages` | GET `[AdminController::class, 'dashMessages']` | `admin` | *Dashboard Messages, Dash Messages, Admin Announcements, Broadcasts* | Yes |
| **Member Management** | `Member Management` | Submenu (`has-sub`) | `Members Details` | `admin_member_details` | `/admin/member-details` | GET `[AdminController::class, 'details']` | `admin` | *Members Details, Member Details, User List, All Members, Member Details Kholo* | Yes |
| | | | `Account Control` | `admin_account_control` | `/admin/account-control` | GET `[AdminController::class, 'accountControl']` | `admin` | *Account Control, Member Account Control, Block User, User Status* | Yes |
| | | | `Members Security` | `admin_member_security` | `/admin/member-security` | GET `[AdminController::class, 'security']` | `admin` | *Members Security, Member Security, Reset Member Password, User Security* | Yes |
| | | | `Zero Pin Members` | `admin_free_pin_account` | `/admin/free-pin-account` | GET `[AdminController::class, 'freePinAccount']` | `admin` | *Zero Pin Members, Free Pin Account, Free Pin Members, Pin Accounts* | Yes |
| | | | `Member Wallet Address` | `admin_wallet_address` | `/admin/wallet-address` | GET `[AdminController::class, 'walletAddress']` | `admin` | *Member Wallet Address, Users Wallet Address, Admin Wallet Address* | Yes |
| **Funds Management** | `Funds Management` | Submenu (`has-sub`) | `Add Funds` | `admin_funds_add` | `/admin/funds/add-funds` | GET `[AdminController::class, 'addFunds']` | `admin` | *Add Funds, Credit Funds, Admin Add Fund, Member ko fund do* | Yes |
| | | | `Deduct Funds` | `admin_funds_deduct` | `/admin/funds/deduct-funds` | GET `[AdminController::class, 'deductFunds']` | `admin` | *Deduct Funds, Debit Funds, Admin Deduct Fund, Fund Kaato* | Yes |
| | | | `Funds Details` | `admin_funds_add_details` | `/admin/funds/add-funds-details` | GET `[AdminController::class, 'addFundsDetails']` | `admin` | *Funds Details, Funds History, Credit Debit History, Fund Transfer Details* | Yes |
| | | | `Import Fund History` | `admin_request_history` | `/admin/request-history` | GET `[AdminController::class, 'requestHistory']` | `admin` | *Import Fund History, Request History, Deposit Requests History* | Yes |
| **Staking Section** | `Staking Section` | Submenu (`has-sub`) | `Staking Income` | `admin_staking_incomes` | `/admin/incomes/staking-incomes` | GET `[AdminController::class, 'stakingIncomes']` | `admin` | *Admin Staking Income, Staking Incomes Report, Staking ROI Report* | Yes |
| | | | `Staking Details` | `admin_staking_details` | `/admin/incomes/staking-details` | GET `[AdminController::class, 'stakingDetails']` | `admin` | *Admin Staking Details, Staking Records, All Member Stakings* | Yes |
| **Income Section** | `Income Section` | Submenu (`has-sub`) | `Level Incomes` | `admin_income_level` | `/admin/incomes/level-incomes` | GET `[AdminController::class, 'levelIncomes']` | `admin` | *Admin Level Incomes, Level Income Report, All Level Earnings* | Yes |
| | | | `Reward Income` | `admin_income_reward` | `/admin/incomes/reward-incomes` | GET `[AdminController::class, 'rewardIncomes']` | `admin` | *Admin Reward Income, Reward Incomes Report, Member Rewards Payout* | Yes |
| | | | `Direct Income` | `admin_income_direct` | `/admin/incomes/direct-incomes` | GET `[AdminController::class, 'directIncomes']` | `admin` | *Admin Direct Income, Direct Income Report, Referral Payout Report* | Yes |
| **Payment Management** | `Payment Management` | Submenu (`has-sub`) | `New Withdrawal Requests` | `admin_new_withdrawals` | `/admin/new-withdrawal-request` | GET `[AdminController::class, 'withdrawals']` | `admin` | *New Withdrawal Requests, Pending Withdrawals, Withdrawal Approval* | Yes |
| | | | `Cancelled Requests` | `admin_cancelled_requests` | `/admin/cancelled-request` | GET `[AdminController::class, 'cancelledRequest']` | `admin` | *Cancelled Requests, Rejected Withdrawals, Cancelled Withdrawal List* | Yes |
| | | | `Payment History` | `admin_payment_history` | `/admin/payment-history` | GET `[AdminController::class, 'paymentHistory']` | `admin` | *Payment History, Payout History, Withdrawal Completed History* | Yes |
| **Support** | `Support` | Submenu (`has-sub`) | `New Support Tickets` | `admin_support_new` | `/admin/support/new-support-ticket` | GET `[AdminController::class, 'newSupportTicket']` | `admin` | *New Support Tickets, Unassigned Tickets, Incoming Support Requests* | Yes |
| | | | `Open Support Tickets` | `admin_support_open` | `/admin/support/open-support-ticket` | GET `[AdminController::class, 'openSupportTicket']` | `admin` | *Open Support Tickets, Pending Tickets, In-progress Tickets* | Yes |
| | | | `Closed Support Tickets` | `admin_support_close` | `/admin/support/close-support-ticket` | GET `[AdminController::class, 'closeSupportTicket']` | `admin` | *Closed Support Tickets, Resolved Tickets, Completed Tickets* | Yes |
| **Utilities** | `Voice Assistant` | Modal Trigger | — | `voice_assistant_toggle` | `#iraModal` | JS Modal Trigger | `admin`, `member`, `guest` | *Voice Assistant, Voice AI, Assistant, IRA, माइक, असिस्टेंट* | UI Action |
| **Utilities** | `Logout` | Action | — | `admin_logout` | `/admin/logout` | GET `[AdminController::class, 'logout']` | `admin` | *Admin Logout, Sign Out, Log Out, Admin Bahar Niklo* | Action / Nav |

---

## 3. Public Frontend Navigation Inventory

Accessible without authentication (`guest` role).

| Visible Label | Destination Key (`destination_key`) | Verified Target URL | Route / Controller Method | Authorized Roles | Spoken Aliases (EN / Hinglish / HI) |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `Website Home` | `home` | `/` | GET `[FrontendController::class, 'index']` | `all` | *Frontend, Website, Main Website, Public Home, Homepage, होम पेज* |
| `Gallery` | `gallery` | `/gallery` | GET `[FrontendController::class, 'gallery']` | `all` | *Gallery, Photo Gallery, Public Gallery, गैलरी* |
| `Member Login` | `member_login` | `/member` | GET `[MemberController::class, 'memberPage']` | `all` | *Member Login, Sign In, User Login, Login Page, लॉगिन, साइन इन* |
| `Admin Login` | `admin_login` | `/admin` | GET `[AdminController::class, 'adminPage']` | `all` | *Admin Login, Administrator Sign In, Admin Portal, एडमिन लॉगिन* |
| `Admin Password Reset` | `forget_password` | `/forget-password` | GET `[AdminController::class, 'forgetPassword']` | `guest`, `admin` | *Admin Password Reset, Forget Password, पासवर्ड रीसेट* |
| `Member Password Reset` | `member_forget_password` | `/member-forget-password` | GET `[MemberDetailController::class, 'memforgetPassword']` | `guest`, `member` | *Member Password Reset, Password Bhool Gaye, मेंबर पासवर्ड रीसेट* |

> **Inventory Verification Note (Phase 6):** Laravel route inspection revealed that user registration route is `GET /member/register/{id}` (requiring sponsor ID parameter). There is no standalone `/member/sign-up` GET route. General registration inquiries route to `/member`.

---

---

## 4. Non-Navigable Sensitive Actions (Strictly Blocked from Voice Navigation)

These routes are state-changing, transactional, authentication, or mutation POST endpoints. They MUST NEVER be targeted by voice navigation, and the voice assistant MUST reject any command attempting to trigger transactions or mutations.

| Category / Purpose | HTTP Method & Route | Controller Action | Target Description / Security Risk |
| :--- | :--- | :--- | :--- |
| **Authentication** | POST `/adminLogin` | `AdminController::authenticate` | Admin authentication handler |
| | POST `/retrivePassword` | `AdminController::retrivePassword` | Admin password recovery |
| | POST `/userLogin` | `MemberDetailController::authenticate` | Member login handler |
| | POST `/userRegister` | `MemberDetailController::userregister` | Member registration submission |
| | POST `/memretrivePassword` | `MemberDetailController::memretrivePassword` | Member password retrieval |
| **Profile & Settings** | POST `/insertProfile` | `MemberDetailController::insertPdata` | Updates member profile data |
| | POST `/emailChange` | `MemberDetailController::emailChange` | Modifies member email address |
| | POST `/passChange` | `MemberDetailController::passChange` | Modifies member login password |
| | POST `/txnpassChange` | `MemberDetailController::txnpassChange` | Modifies transaction password |
| | POST `/insertWalletAddress` | `MemberDetailController::insertWalletAddress` | Updates member crypto wallet address |
| | POST `/securityUpdate` | `AdminController::securityUpdate` | Admin updates member security |
| | POST `/updateDetails` | `AdminController::updateDetails` | Admin updates member account details |
| | POST `/updateWallet` | `AdminController::updateWallet` | Admin updates wallet records |
| | POST `/changePassword` | `AdminController::change` | Admin password change |
| | POST `/changeEmail` | `AdminController::changeEmail` | Admin email change |
| **Financial / Staking / Funds** | POST `/FundReq` | `FundController::FundReq` | Submits new fund deposit request |
| | POST `/USDTFundReq` | `FundController::USDTFundReq` | Submits USDT fund deposit request |
| | POST `/instantFunds` | `FundController::instantFunds` | Processes instant fund addition |
| | POST `/fundTrans` | `FundController::fundTrans` | Initiates fund transfer |
| | POST `/walletP2pTransfer` | `FundController::walletP2pTransfer` | Initiates Wallet to P2P balance transfer |
| | POST `/createStaking` | `StakingController::createStaking` | Creates new staking contract |
| | POST `/createStakingTopup` | `StakingController::createStakingTopup` | Tops up existing staking contract |
| | POST `/claimROI` | `StakingController::claimROI` | Claims staking return-on-investment |
| | POST `/addMemFunds` | `AdminController::addMemFunds` | Admin credits funds to user balance |
| | POST `/deductMemFunds` | `AdminController::deductMemFunds` | Admin debits funds from user balance |
| **Withdrawals** | POST `/initWithdrawal` | `WithdrawalController::initWithdrawal` | Pre-validates withdrawal parameters |
| | POST `/balValidate` | `WithdrawalController::balValidate` | Validates balance before withdrawal |
| | POST `/initiate-withdrawal` | `WithdrawalController::withdrawal` | Finalizes member withdrawal request |
| | POST `/sendWdlRequest` | `WithdrawalController::sendWdlRequest` | Submits withdrawal request |
| | POST `/withdrawal/accept/{id}` | `AdminController::wAccept` | Admin approves withdrawal request |
| | POST `/withdrawal/cancel/{id}` | `AdminController::wCancel` | Admin cancels withdrawal request |
| **Support & Admin Tools** | POST `/createTicket` | `SupportController::createTicket` | Submits new support ticket |
| | POST `/MbReplyTicket` | `SupportController::MbReplyTicket` | Submits member reply to ticket |
| | POST `/Ticket` | `SupportController::Ticket` | Admin responds to support ticket |
| | POST `/PinUserAccount` | `AdminController::PinUserAccount` | Admin pins or alters user account tier |
| | POST `/dashMsg` | `AdminController::dashMsg` | Admin publishes dashboard broadcast |
| | POST `/popup-image/update` | `AdminController::popupImageUpdate` | Admin updates popup modal banner |
| | POST `/acheivImage` | `AdminController::acheivImage` | Admin uploads achiever image |
| | POST `/rateUpdate` | `AdminController::rateUpdate` | Admin updates exchange/conversion rate |
| | POST `/packages/{id}/status` | `AdminController::packageStatusChange` | Admin enables/disables package |
| | POST `/galleryUpdate` | `MemberDetailController::galleryUpdate` | Admin updates public gallery images |

---

## 5. Key Architectural Insights for Natural Voice Section Resolution

1. **All Submenu Parents have no direct destination URL:** In both Member and Admin, selecting a submenu parent (e.g., `Earnings`, `Member Management`, `Funds Management`) in the UI only toggles the collapsible accordion menu. Therefore, when a user asks about or commands a parent section, IRA must treat it as a **Section Discovery Inquiry**, listing its child pages and prompting the user for their desired choice.
2. **Security Isolation:** Member roles inquiring about Admin sections (`Member Management`, `Funds Management`, `Payment Management`, `Income Section`, `Staking Section`) are authoritatively denied access with an `unauthorized` intent.
3. **Phonetic & Aliasing Resiliency:** Speech recognition frequently transcribes acronyms or technical terms inconsistently (e.g. `P2P` $\to$ `Petrol`, `Geneology` $\to$ `Tree`, `Import Fund` $\to$ `Add Fund`). The inventory incorporates these proven phonetic aliases.
