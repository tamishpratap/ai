<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\LevelsController;
use App\Http\Controllers\MemberDetailController;
use App\Http\Controllers\StakingController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [FrontendController::class, 'index']);
Route::get('gallery', [FrontendController::class, 'gallery']);
Route::get('/send-admin-otp', [AdminController::class, 'sendAdminOtp']);
Route::get('/admin', [AdminController::class, 'adminPage']);
Route::post('adminLogin', [AdminController::class, 'authenticate'])->name('adminLogin');



//forget password
Route::get('/forget-password', [AdminController::class, 'forgetPassword']);
Route::post('/retrivePassword', [AdminController::class, 'retrivePassword'])->name('retrivePassword');

Route::group(['prefix' => 'admin', 'middleware' => 'AdminAuth'], function () {

  Route::get('/dashboard', [AdminController::class, 'dashboard']);
  Route::get('/profile', [AdminController::class, 'profile']);
  Route::get('/member-details', [AdminController::class, 'details']);
  //member account control update
  Route::get('/account-control', [AdminController::class, 'accountControl']);
  Route::get('/account-control/{id}', [AdminController::class, 'statusChange']);
  //member security updates
  Route::get('/member-security', [AdminController::class, 'security']);
  Route::post('securityUpdate', [AdminController::class, 'securityUpdate'])->name('securityUpdate');
  //member details update
  Route::get('/members/member-update/{id}', [AdminController::class, 'memberUpdate']);
  Route::post('/updateDetails', [AdminController::class, 'updateDetails'])->name('updateDetails');

  // dashboard message section
  Route::get('/dash-messages', [AdminController::class, 'dashMessages']);
  Route::get('/dash-messages/delete/{id}', [AdminController::class, 'dashMsgDelete']);

  Route::get('member-login/{id}', [AdminController::class, 'memberLogin']);
  Route::get('/logout', [AdminController::class, 'logout']);

  // //wallet address update
  Route::get('/wallet-address', [AdminController::class, 'walletAddress']);
  Route::get('/new-withdrawal-request', [AdminController::class, 'withdrawals']);
  Route::get('/withdrawal/withdrawal-comment/{id}', [AdminController::class, 'withdrawalComment'])->name('withdrawal.comment');
  Route::get('/cancelled-request', [AdminController::class, 'cancelledRequest']);
  Route::get('/payment-history', [AdminController::class, 'paymentHistory']);
  // Route::get('/gateway-request', [AdminController::class, 'gatewayRequest']);

  Route::post('/sendWdlRequest', [WithdrawalController::class, 'sendWdlRequest'])->name('sendWdlRequest');
  Route::post('/withdrawal/accept/{id}', [AdminController::class, 'wAccept'])->name('withdrawal.accept');
  Route::post('/withdrawal/cancel/{id}', [AdminController::class, 'wCancel'])->name('withdrawal.cancel');

  Route::get('/package-details', [AdminController::class, 'packageDetails']);
  // Route::get('/new-withdrawal-request', [AdminController::class, 'withdrawals']);
  Route::get('/p2p-fund-transfer', [AdminController::class, 'p2pFundTransfer']);
  // Route::get('/payment-history', [AdminController::class, 'paymentHistory']);


  // Income routes
  Route::get('/incomes/staking-details', [AdminController::class, 'stakingDetails']);
  Route::get('/incomes/staking-incomes', [AdminController::class, 'stakingIncomes']);
  Route::get('/incomes/level-incomes', [AdminController::class, 'levelIncomes']);
  Route::get('/incomes/level-details', [AdminController::class, 'levelDetails']);

  //support ticket sections
  Route::get('/support/new-support-ticket', [SupportController::class, 'newSupport']);
  Route::get('/support/open-support-ticket', [SupportController::class, 'openSupport']);
  Route::get('/support/close-support-ticket', [SupportController::class, 'closeSupport']);
  Route::get('/support/view-support-ticket/{id}', [SupportController::class, 'viewSupport']);
  Route::get('/support/view-closed-ticket/{id}', [SupportController::class, 'viewClosed']);
  Route::get('/closed-ticket/{id}', [SupportController::class, 'closedTicket']);
  Route::post('/Ticket', [SupportController::class, 'Ticket'])->name('Ticket');

  //funds
  Route::get('/accept-request/{id}', [FundController::class, 'acceptReq']);
  Route::get('/reject-request/{id}', [FundController::class, 'rejectReq']);
  Route::get('/funds/add-funds', [AdminController::class, 'addFunds']);
  Route::post('addMemFunds', [AdminController::class, 'addMemFunds'])->name('addMemFunds');

  // deposit details
  Route::get('/deposit-details', [AdminController::class, 'depDetails']);
  Route::get('/deposit-status/{id}', [AdminController::class, 'depositStatus']);
  Route::post('/depositDataUSDT', [AdminController::class, 'depositDataUSDT'])->name('depositDataUSDT');

  Route::get('/funds/deduct-funds', [AdminController::class, 'deductFunds']);
  Route::post('deductMemFunds', [AdminController::class, 'deductMemFunds'])->name('deductMemFunds');
  Route::get('/funds/add-funds-details', [AdminController::class, 'addFundsDetails']);
  Route::get('/request-history', [AdminController::class, 'fundsHistory']);
  Route::get('/fund-requests', [AdminController::class, 'fundRequests']);
  Route::get('/funds/import-fund-history', [AdminController::class, 'requestsHistory']);
  Route::get('/accept-fundrequest/{id}', [FundController::class, 'acceptReq']);
  Route::get('/reject-fundrequest/{id}', [FundController::class, 'rejectReq']);

  //POST ROUTES
  Route::post('/dashMsg', [AdminController::class, 'dashMsg'])->name('dashMsg');
  Route::post('/changePassword', [AdminController::class, 'change'])->name('changePassword');
  Route::post('/changeEmail', [AdminController::class, 'changeEmail'])->name('changeEmail');
  Route::post('/updateWallet', [AdminController::class, 'updateWallet'])->name('updateWallet');
  Route::post('/acheivImage', [AdminController::class, 'acheivImage'])->name('acheivImage');

  //set rates
  Route::get('/rate/set-rates', [AdminController::class, 'setRates']);
  Route::post('/rateUpdate', [AdminController::class, 'rateUpdate'])->name('rateUpdate');

  //member-gallery
  Route::get('/member-gallery', [AdminController::class, 'memGallery']);
  Route::get('/member-gallery/status-change/{id}', [AdminController::class, 'memGalStatusChange']);
});

//Member Routes
Route::get('/member', [MemberDetailController::class, 'index']);
Route::get('/member/register/{id}', [MemberDetailController::class, 'signup']);
Route::get('/member/account-created', [MemberDetailController::class, 'accountCreated']);
Route::post('/userLogin', [MemberDetailController::class, 'authenticate'])->name('userLogin');
Route::post('/userRegister', [MemberDetailController::class, 'userregister'])->name('userRegister');
Route::post('/getSponname', [MemberDetailController::class, 'getSponname']);
Route::post('/getMemberid', [MemberDetailController::class, 'getMemberid']);
Route::post('/getMember', [MemberDetailController::class, 'getMember']);
Route::post('/getUsername', [MemberDetailController::class, 'getUsername']);
// Route::get('member/download/business-plan', [MemberDetailController::class, 'downloads']);
Route::get('/member-forget-password', [MemberDetailController::class, 'memforgetPassword']);
Route::post('/memretrivePassword', [MemberDetailController::class, 'memretrivePassword'])->name('memretrivePassword');

Route::group(['prefix' => 'member', 'middleware' => 'MemberAuth'], function () {

  Route::get('/dashboard', [MemberDetailController::class, 'dashboard']);
  Route::get('/logout', [MemberDetailController::class, 'logout']);

  Route::get('/trading/arbitrage-trading', [MemberDetailController::class, 'trading']);


  // Profile Routes
  Route::get('/profile/user-summary', [MemberDetailController::class, 'userSummary']);
  Route::post('insertProfile', [MemberDetailController::class, 'insertPdata'])->name('insertProfile');
  Route::get('/profile/security', [MemberDetailController::class, 'security']);
  Route::post('emailChange', [MemberDetailController::class, 'emailChange'])->name('emailChange');
  Route::post('passChange', [MemberDetailController::class, 'passChange'])->name('passChange');
  Route::post('txnpassChange', [MemberDetailController::class, 'txnpassChange'])->name('txnpassChange');
  Route::get('/profile/wallet-address', [MemberDetailController::class, 'walletAddress']);
  Route::post('insertWalletAddress', [MemberDetailController::class, 'insertWalletAddress'])->name('insertWalletAddress');

  // Create User
  Route::get('/create-member/{memberid}/{side}', [MemberDetailController::class, 'createUser']);
  Route::post('userCreate', [MemberDetailController::class, 'userCreate'])->name('userCreate');


  // import route
  Route::get('/deposit/fund-request', [FundController::class, 'fundRequest']);
  Route::get('/deposit/request-history', [FundController::class, 'requestHistory']);
  Route::post('FundReq', [FundController::class, 'FundReq'])->name('FundReq');

  //Import Fund Section
  Route::get('/fund/import-fund', [FundController::class, 'importfund']);
  Route::post('USDTFundReq', [FundController::class, 'USDTFundReq'])->name('USDTFundReq');
  Route::post('instantFunds', [FundController::class, 'instantFunds'])->name('instantFunds');
  Route::get('/payment-success/{orderid}', [FundController::class, 'paySuccess']);
  Route::get('/payment-cancel/{orderid}', [FundController::class, 'payCancel']);
  Route::get('/fund/import-fund-history', [FundController::class, 'impfundhis']);

  //income route
  Route::get('/income/staking-details', [StakingController::class, 'stakingDetails']);
  Route::get('/income/staking-incomes', [StakingController::class, 'stakingIncomes']);
  Route::get('/income/level-details', [IncomeController::class, 'levelDetails']);
  Route::get('/income/level-incomes', [IncomeController::class, 'levelIncomes']);

  // P2p Fund Transfer
  Route::get('/p2p-wallet', [FundController::class, 'p2pwallet']);
  Route::post('fundTrans', [FundController::class, 'fundTrans'])->name('fundTrans');
  Route::get('/p2p-wallet-history', [FundController::class, 'p2pHistory']);

  //break route
  Route::get('/break-amount/{id}', [StakingController::class, 'breakAmount']);

  // wallet Section
  Route::get('/wallet', [WithdrawalController::class, 'mainWallets']);
  Route::get('/send-withdrawal-otp', [WithdrawalController::class, 'sendWithdrawalOtp']);
  Route::post('initWithdrawal', [WithdrawalController::class, 'initWithdrawal'])->name('initWithdrawal');
  Route::get('/wallet/coin-wallet', [WithdrawalController::class, 'coinWallets']);
  Route::get('/wallet/all-transaction', [WithdrawalController::class, 'walletTransfer']);
  Route::get('/wallet/wallet-history', [WithdrawalController::class, 'withdrlHistory']);

  // staking route
  Route::get('/staking', [StakingController::class, 'staking']);
  Route::get('/staking/staking-topup', [StakingController::class, 'stakingTopup']);
  Route::get('/staking/staking-income', [StakingController::class, 'stakingIncome']);
  // Route::get('/staking/compounding-income', [StakingController::class, 'compoundingIncome']);
  Route::get('/staking/staking-details', [StakingController::class, 'stakingDetails']);
  Route::get('/staking/break-staking/{id}', [StakingController::class, 'stakingBreak']);
  Route::post('createStaking', [StakingController::class, 'createStaking'])->name('createStaking');
  Route::post('createStakingTopup', [StakingController::class, 'createStakingTopup'])->name('createStakingTopup');
  Route::post('claimROI', [StakingController::class, 'claimROI'])->name('claimROI');


  //Direct and geneology
  Route::get('/team/geneology', [LevelsController::class, 'geneology']);
  Route::get('/team/view-geneology/{id}', [LevelsController::class, 'ViewGeneology']);
  Route::post('/searchGenelogy', [LevelsController::class, 'searchGenelogy'])->name('searchGenelogy');
  Route::get('/levels/direct-members', [LevelsController::class, 'directMembers']);
  Route::get('/levels/level-members-table', [LevelsController::class, 'lelMemTable']);
  Route::get('/levels/level-members-details/{levels}', [LevelsController::class, 'lelMemDetails']);
  Route::get('/levels/total-team-members', [LevelsController::class, 'totalTeamMembers']); //new route for


  //   //wallet to p2p transfer
  //   Route::get('/wallet/wallet-to-p2p', [FundController::class, 'walletToP2P']);
  //   Route::get('/wallet/wallet-to-p2p-history', [FundController::class, 'walletP2Phistory']);
  //   Route::post('walletP2pTransfer', [FundController::class, 'walletP2pTransfer'])->name('walletP2pTransfer');

  //member support section
  Route::get('/support/create-ticket', [SupportController::class, 'createTick']);
  Route::get('/support/support-tickets', [SupportController::class, 'supportTickets']);
  Route::get('/support/view-support-ticket/{tid}', [SupportController::class, 'ViewsupportTickets']);
  Route::post('/createTicket', [SupportController::class, 'createTicket'])->name('createTicket');
  Route::post('/MbReplyTicket', [SupportController::class, 'MbReplyTicket'])->name('MbReplyTicket');

  Route::post('balValidate', [WithdrawalController::class, 'balValidate'])->name('balValidate');
  Route::post('initiate-withdrawal', [WithdrawalController::class, 'withdrawal'])->name('initiate-withdrawal');

  //gallery
  Route::get('/gallery/member-gallery', [MemberDetailController::class, 'gallery']);
  Route::post('/galleryUpdate', [MemberDetailController::class, 'galleryUpdate'])->name('galleryUpdate');
});

Route::fallback(function () {
  return view('member.error404');
});
