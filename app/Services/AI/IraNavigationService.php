<?php

namespace App\Services\AI;

use App\Services\AI\AppNavigationOntology;
use App\Services\AI\IraFormRegistry;
use App\Services\AI\IraFormUnderstandingService;
use App\Services\AI\NavigationGeminiBridge;
use App\Services\Gemini\GeminiService;
use Illuminate\Support\Facades\Log;

class IraNavigationService
{
    /**
     * Authoritative registry of server-defined navigation destinations.
     * Mapped directly from AI Demo's actual routes and sidebar menus.
     */
    protected array $destinations = [
        // ==========================================
        // Public Destinations
        // ==========================================
        'home' => [
            'path' => '/',
            'roles' => ['guest', 'member', 'admin'],
            'label' => 'Home Page',
            'section' => 'public',
            'panel' => 'public',
        ],
        'gallery' => [
            'path' => '/gallery',
            'roles' => ['guest', 'member', 'admin'],
            'label' => 'Public Gallery',
            'section' => 'public',
            'panel' => 'public',
        ],
        'member_login' => [
            'path' => '/member',
            'roles' => ['guest', 'member', 'admin'],
            'label' => 'Member Sign In',
            'section' => 'public',
            'panel' => 'public',
        ],
        'admin_login' => [
            'path' => '/admin',
            'roles' => ['guest', 'member', 'admin'],
            'label' => 'Administrator Sign In',
            'section' => 'public',
            'panel' => 'public',
        ],
        'forget_password' => [
            'path' => '/forget-password',
            'roles' => ['guest', 'admin'],
            'label' => 'Admin Password Reset',
            'section' => 'public',
            'panel' => 'public',
        ],
        'member_forget_password' => [
            'path' => '/member-forget-password',
            'roles' => ['guest', 'member'],
            'label' => 'Member Password Reset',
            'section' => 'public',
            'panel' => 'public',
        ],

        // ==========================================
        // Member Protected Destinations (from Member Sidebar)
        // ==========================================
        'member_dashboard' => [
            'path' => '/member/dashboard',
            'roles' => ['member'],
            'label' => 'Dashboard',
            'section' => 'dashboard',
            'panel' => 'member',
        ],
        'member_trading' => [
            'path' => '/member/trading/arbitrage-trading',
            'roles' => ['member'],
            'label' => 'Arbitrage Trading',
            'section' => 'trading',
            'panel' => 'member',
        ],
        'member_profile_summary' => [
            'path' => '/member/profile/user-summary',
            'roles' => ['member'],
            'label' => 'Edit Profile',
            'section' => 'profile',
            'panel' => 'member',
        ],
        'member_profile_security' => [
            'path' => '/member/profile/security',
            'roles' => ['member'],
            'label' => 'Change Password',
            'section' => 'profile',
            'panel' => 'member',
        ],
        'member_profile_wallet' => [
            'path' => '/member/profile/wallet-address',
            'roles' => ['member'],
            'label' => 'Settings (Wallet Address)',
            'section' => 'profile',
            'panel' => 'member',
        ],
        'member_fund_import' => [
            'path' => '/member/fund/import-fund',
            'roles' => ['member'],
            'label' => 'Add Fund',
            'section' => 'add_fund',
            'panel' => 'member',
        ],
        'member_fund_history' => [
            'path' => '/member/fund/import-fund-history',
            'roles' => ['member'],
            'label' => 'Add Fund History',
            'section' => 'add_fund',
            'panel' => 'member',
        ],
        'member_staking' => [
            'path' => '/member/staking',
            'roles' => ['member'],
            'label' => 'Create Investment',
            'section' => 'investment',
            'panel' => 'member',
        ],
        'member_staking_details' => [
            'path' => '/member/staking/staking-details',
            'roles' => ['member'],
            'label' => 'Investment Details',
            'section' => 'investment',
            'panel' => 'member',
        ],
        'member_income_staking' => [
            'path' => '/member/income/staking-incomes',
            'roles' => ['member'],
            'label' => 'Staking Income',
            'section' => 'earnings',
            'panel' => 'member',
        ],
        'member_income_level' => [
            'path' => '/member/income/level-incomes',
            'roles' => ['member'],
            'label' => 'Level Income',
            'section' => 'earnings',
            'panel' => 'member',
        ],
        'member_income_direct' => [
            'path' => '/member/income/direct-incomes',
            'roles' => ['member'],
            'label' => 'Direct Income',
            'section' => 'earnings',
            'panel' => 'member',
        ],
        'member_income_reward' => [
            'path' => '/member/income/reward-incomes',
            'roles' => ['member'],
            'label' => 'Reward Income',
            'section' => 'reward_section',
            'panel' => 'member',
        ],
        'member_income_reward_details' => [
            'path' => '/member/income/reward-details',
            'roles' => ['member'],
            'label' => 'Reward Details',
            'section' => 'reward_section',
            'panel' => 'member',
        ],
        'member_team_geneology' => [
            'path' => '/member/team/geneology',
            'roles' => ['member'],
            'label' => 'Geneology',
            'section' => 'team_management',
            'panel' => 'member',
        ],
        'member_team_direct' => [
            'path' => '/member/levels/direct-members',
            'roles' => ['member'],
            'label' => 'Direct Members',
            'section' => 'team_management',
            'panel' => 'member',
        ],
        'member_team_level_table' => [
            'path' => '/member/levels/level-members-table',
            'roles' => ['member'],
            'label' => 'Level Member Table',
            'section' => 'team_management',
            'panel' => 'member',
        ],
        'member_wallet_main' => [
            'path' => '/member/wallet',
            'roles' => ['member'],
            'label' => 'Withdrawal Fund',
            'section' => 'withdrawal_section',
            'panel' => 'member',
        ],
        'member_wallet_history' => [
            'path' => '/member/wallet/wallet-history',
            'roles' => ['member'],
            'label' => 'Withdrawal History',
            'section' => 'withdrawal_section',
            'panel' => 'member',
        ],
        'member_wallet_to_p2p' => [
            'path' => '/member/wallet/wallet-to-p2p',
            'roles' => ['member'],
            'label' => 'Wallet to P2P Transfer',
            'section' => 'withdrawal_section',
            'panel' => 'member',
        ],
        'member_wallet_transactions' => [
            'path' => '/member/wallet/all-transaction',
            'roles' => ['member'],
            'label' => 'Transaction History',
            'section' => 'withdrawal_section',
            'panel' => 'member',
        ],
        'member_p2p_wallet' => [
            'path' => '/member/p2p-wallet',
            'roles' => ['member'],
            'label' => 'P2P Wallet',
            'section' => 'p2p',
            'panel' => 'member',
        ],
        'member_p2p_history' => [
            'path' => '/member/p2p-wallet-history',
            'roles' => ['member'],
            'label' => 'P2P History',
            'section' => 'p2p',
            'panel' => 'member',
        ],
        'member_support_create' => [
            'path' => '/member/support/create-ticket',
            'roles' => ['member'],
            'label' => 'Create New Ticket',
            'section' => 'support',
            'panel' => 'member',
        ],
        'member_support_tickets' => [
            'path' => '/member/support/support-tickets',
            'roles' => ['member'],
            'label' => 'Support Tickets',
            'section' => 'support',
            'panel' => 'member',
        ],
        'member_logout' => [
            'path' => '/member/logout',
            'roles' => ['member'],
            'label' => 'Logout',
            'section' => 'logout',
            'panel' => 'member',
        ],

        // ==========================================
        // Admin Protected Destinations (from Admin Sidebar)
        // ==========================================
        'admin_dashboard' => [
            'path' => '/admin/dashboard',
            'roles' => ['admin'],
            'label' => 'Dashboard',
            'section' => 'dashboard',
            'panel' => 'admin',
        ],
        'admin_popup_image' => [
            'path' => '/admin/popup-image',
            'roles' => ['admin'],
            'label' => 'Popup / Dashboard Image',
            'section' => 'dashboard',
            'panel' => 'admin',
        ],
        'admin_dash_messages' => [
            'path' => '/admin/dash-messages',
            'roles' => ['admin'],
            'label' => 'Dashboard Messages',
            'section' => 'dashboard',
            'panel' => 'admin',
        ],
        'admin_profile' => [
            'path' => '/admin/profile',
            'roles' => ['admin'],
            'label' => 'Administrator Profile',
            'section' => 'profile',
            'panel' => 'admin',
        ],
        'admin_member_details' => [
            'path' => '/admin/member-details',
            'roles' => ['admin'],
            'label' => 'Members Details',
            'section' => 'member_management',
            'panel' => 'admin',
        ],
        'admin_account_control' => [
            'path' => '/admin/account-control',
            'roles' => ['admin'],
            'label' => 'Account Control',
            'section' => 'member_management',
            'panel' => 'admin',
        ],
        'admin_member_security' => [
            'path' => '/admin/member-security',
            'roles' => ['admin'],
            'label' => 'Members Security',
            'section' => 'member_management',
            'panel' => 'admin',
        ],
        'admin_free_pin_account' => [
            'path' => '/admin/free-pin-account',
            'roles' => ['admin'],
            'label' => 'Zero Pin Members',
            'section' => 'member_management',
            'panel' => 'admin',
        ],
        'admin_wallet_address' => [
            'path' => '/admin/wallet-address',
            'roles' => ['admin'],
            'label' => 'Member Wallet Address',
            'section' => 'member_management',
            'panel' => 'admin',
        ],
        'admin_funds_add' => [
            'path' => '/admin/funds/add-funds',
            'roles' => ['admin'],
            'label' => 'Add Funds',
            'section' => 'funds_management',
            'panel' => 'admin',
        ],
        'admin_funds_deduct' => [
            'path' => '/admin/funds/deduct-funds',
            'roles' => ['admin'],
            'label' => 'Deduct Funds',
            'section' => 'funds_management',
            'panel' => 'admin',
        ],
        'admin_funds_add_details' => [
            'path' => '/admin/funds/add-funds-details',
            'roles' => ['admin'],
            'label' => 'Funds Details',
            'section' => 'funds_management',
            'panel' => 'admin',
        ],
        'admin_request_history' => [
            'path' => '/admin/request-history',
            'roles' => ['admin'],
            'label' => 'Import Fund History',
            'section' => 'funds_management',
            'panel' => 'admin',
        ],
        'admin_staking_incomes' => [
            'path' => '/admin/incomes/staking-incomes',
            'roles' => ['admin'],
            'label' => 'Staking Income',
            'section' => 'staking_section',
            'panel' => 'admin',
        ],
        'admin_staking_details' => [
            'path' => '/admin/incomes/staking-details',
            'roles' => ['admin'],
            'label' => 'Staking Details',
            'section' => 'staking_section',
            'panel' => 'admin',
        ],
        'admin_income_level' => [
            'path' => '/admin/incomes/level-incomes',
            'roles' => ['admin'],
            'label' => 'Level Incomes',
            'section' => 'income_section',
            'panel' => 'admin',
        ],
        'admin_income_reward' => [
            'path' => '/admin/incomes/reward-incomes',
            'roles' => ['admin'],
            'label' => 'Reward Income',
            'section' => 'income_section',
            'panel' => 'admin',
        ],
        'admin_income_direct' => [
            'path' => '/admin/incomes/direct-incomes',
            'roles' => ['admin'],
            'label' => 'Direct Income',
            'section' => 'income_section',
            'panel' => 'admin',
        ],
        'admin_new_withdrawals' => [
            'path' => '/admin/new-withdrawal-request',
            'roles' => ['admin'],
            'label' => 'New Withdrawal Requests',
            'section' => 'payment_management',
            'panel' => 'admin',
        ],
        'admin_cancelled_requests' => [
            'path' => '/admin/cancelled-request',
            'roles' => ['admin'],
            'label' => 'Cancelled Requests',
            'section' => 'payment_management',
            'panel' => 'admin',
        ],
        'admin_payment_history' => [
            'path' => '/admin/payment-history',
            'roles' => ['admin'],
            'label' => 'Payment History',
            'section' => 'payment_management',
            'panel' => 'admin',
        ],
        'admin_support_new' => [
            'path' => '/admin/support/new-support-ticket',
            'roles' => ['admin'],
            'label' => 'New Support Tickets',
            'section' => 'support',
            'panel' => 'admin',
        ],
        'admin_support_open' => [
            'path' => '/admin/support/open-support-ticket',
            'roles' => ['admin'],
            'label' => 'Open Support Tickets',
            'section' => 'support',
            'panel' => 'admin',
        ],
        'admin_support_close' => [
            'path' => '/admin/support/close-support-ticket',
            'roles' => ['admin'],
            'label' => 'Closed Support Tickets',
            'section' => 'support',
            'panel' => 'admin',
        ],
        'admin_logout' => [
            'path' => '/admin/logout',
            'roles' => ['admin'],
            'label' => 'Logout',
            'section' => 'logout',
            'panel' => 'admin',
        ],
    ];

    /**
     * Parent Sidebar Sections inventory for Section Discovery.
     */
    protected array $sidebarSections = [
        'member' => [
            'profile' => [
                'name' => 'Profile',
                'aliases' => ['profile', 'profile section', 'my profile', 'meri profile', 'account', 'user summary'],
                'pages' => [
                    'Edit Profile' => 'member_profile_summary',
                    'Change Password' => 'member_profile_security',
                    'Settings (Wallet Address)' => 'member_profile_wallet',
                ],
            ],
            'add_fund' => [
                'name' => 'Add Fund',
                'aliases' => ['add fund', 'add funds', 'fund', 'funds', 'fund section', 'funds section', 'deposit', 'import fund', 'add fund section'],
                'pages' => [
                    'Add Fund' => 'member_fund_import',
                    'Add Fund History' => 'member_fund_history',
                ],
            ],
            'investment' => [
                'name' => 'Investment',
                'aliases' => ['investment', 'investments', 'investment section', 'staking', 'staking section', 'stake'],
                'pages' => [
                    'Create Investment' => 'member_staking',
                    'Investment Details' => 'member_staking_details',
                ],
            ],
            'earnings' => [
                'name' => 'Earnings',
                'aliases' => ['earnings', 'earning', 'income', 'incomes', 'kamai', 'earning section', 'earnings section', 'income section', 'incomes section'],
                'pages' => [
                    'Staking Income' => 'member_income_staking',
                    'Level Income' => 'member_income_level',
                    'Direct Income' => 'member_income_direct',
                ],
            ],
            'reward_section' => [
                'name' => 'Reward Section',
                'aliases' => ['reward', 'rewards', 'reward section', 'rewards section', 'inam'],
                'pages' => [
                    'Reward Income' => 'member_income_reward',
                    'Reward Details' => 'member_income_reward_details',
                ],
            ],
            'team_management' => [
                'name' => 'Team Management',
                'aliases' => ['team', 'teams', 'team section', 'team management', 'network', 'downline', 'levels'],
                'pages' => [
                    'Geneology' => 'member_team_geneology',
                    'Direct Members' => 'member_team_direct',
                    'Level Member Table' => 'member_team_level_table',
                ],
            ],
            'withdrawal_section' => [
                'name' => 'Withdrawal Section',
                'aliases' => ['withdrawal', 'withdrawals', 'wallet', 'wallets', 'nikasi', 'withdraw', 'withdrawal section', 'wallet section'],
                'pages' => [
                    'Withdrawal Fund' => 'member_wallet_main',
                    'Withdrawal History' => 'member_wallet_history',
                    'Wallet to P2P Transfer' => 'member_wallet_to_p2p',
                    'Transaction History' => 'member_wallet_transactions',
                ],
            ],
            'p2p' => [
                'name' => 'P2P',
                'aliases' => ['p2p', 'p to p', 'p-to-p', 'p2p section', 'p to p section', 'peer to peer', 'पी टू पी', 'पी2पी', 'petrol'],
                'pages' => [
                    'P2P Wallet' => 'member_p2p_wallet',
                    'P2P History' => 'member_p2p_history',
                ],
            ],
            'support' => [
                'name' => 'Support',
                'aliases' => ['support', 'support section', 'help desk', 'ticket', 'tickets', 'help'],
                'pages' => [
                    'Create New Ticket' => 'member_support_create',
                    'Support Tickets' => 'member_support_tickets',
                ],
            ],
        ],

        'admin' => [
            'member_management' => [
                'name' => 'Member Management',
                'aliases' => ['member management', 'members', 'member', 'users', 'user management', 'users list', 'member section'],
                'pages' => [
                    'Members Details' => 'admin_member_details',
                    'Account Control' => 'admin_account_control',
                    'Members Security' => 'admin_member_security',
                    'Zero Pin Members' => 'admin_free_pin_account',
                    'Member Wallet Address' => 'admin_wallet_address',
                ],
            ],
            'funds_management' => [
                'name' => 'Funds Management',
                'aliases' => ['funds management', 'fund management', 'funds', 'fund', 'admin funds', 'funds section'],
                'pages' => [
                    'Add Funds' => 'admin_funds_add',
                    'Deduct Funds' => 'admin_funds_deduct',
                    'Funds Details' => 'admin_funds_add_details',
                    'Import Fund History' => 'admin_request_history',
                ],
            ],
            'staking_section' => [
                'name' => 'Staking Section',
                'aliases' => ['staking section', 'staking', 'stake', 'admin staking'],
                'pages' => [
                    'Staking Income' => 'admin_staking_incomes',
                    'Staking Details' => 'admin_staking_details',
                ],
            ],
            'income_section' => [
                'name' => 'Income Section',
                'aliases' => ['income section', 'incomes section', 'income', 'incomes', 'income management', 'admin income'],
                'pages' => [
                    'Level Incomes' => 'admin_income_level',
                    'Reward Income' => 'admin_income_reward',
                    'Direct Income' => 'admin_income_direct',
                ],
            ],
            'payment_management' => [
                'name' => 'Payment Management',
                'aliases' => ['payment management', 'payments', 'payment', 'withdrawals', 'withdrawal', 'payouts', 'payment section'],
                'pages' => [
                    'New Withdrawal Requests' => 'admin_new_withdrawals',
                    'Cancelled Requests' => 'admin_cancelled_requests',
                    'Payment History' => 'admin_payment_history',
                ],
            ],
            'support' => [
                'name' => 'Support',
                'aliases' => ['support', 'support section', 'tickets', 'ticket', 'admin support'],
                'pages' => [
                    'New Support Tickets' => 'admin_support_new',
                    'Open Support Tickets' => 'admin_support_open',
                    'Closed Support Tickets' => 'admin_support_close',
                ],
            ],
        ],
    ];

    public function __construct(
        protected GeminiService $gemini,
        protected ?IraFormUnderstandingService $formUnderstanding = null,
        protected ?NavigationGeminiBridge $navGeminiBridge = null
    ) {
        $this->navGeminiBridge = $this->navGeminiBridge ?? app(NavigationGeminiBridge::class);
        $this->destinations = AppNavigationOntology::getAllDestinations();
        $this->sidebarSections = AppNavigationOntology::getSidebarSections();
    }

    /**
     * Resolve natural user speech or text into a validated navigation intent.
     *
     * @param  string  $speech       Raw speech or typed text (Hindi, Hinglish, English)
     * @param  string  $role         Authoritative server-side role ('guest'|'member'|'admin')
     * @param  string  $currentPage  Current page path (e.g. '/member/dashboard')
     * @param  string  $currentMode  Current assistant mode ('chat')
     * @param  array   $context      Recent conversational context
     * @return array
     */
    public function resolveIntent(
        string $speech,
        string $role = 'guest',
        string $currentPage = '',
        string $currentMode = 'chat',
        array $context = []
    ): array {
        $clean = trim($speech);
        if ($clean === '') {
            return [
                'success' => false,
                'intent' => 'unsupported_action',
                'destination_key' => null,
                'target_url' => null,
                'speech' => 'Kripya boliye ki aap kis page par jaana chahte hain.',
                'response_language' => 'hinglish',
                'confidence' => 0.0,
                'needs_clarification' => false,
                'context' => [],
            ];
        }

        $detectedLang = $this->detectLanguage($clean);

        // 1. Process Pending Clarification / Follow-up context first
        if (!empty($context['pending_clarification'])) {
            $followUpResult = $this->resolvePendingClarification($clean, $role, $detectedLang, $context);
            if ($followUpResult !== null) {
                return $followUpResult;
            }
        }

        // 2. Deterministic Intent & Sidebar Navigation Resolver
        $deterministic = $this->resolveDeterministic($clean, $role, $currentPage, $detectedLang, $context);
        if ($deterministic !== null) {
            return $deterministic;
        }

        // 2b. Form Filling Understanding (when user is on a page with a registered form)
        if ($this->formUnderstanding !== null && !empty($currentPage)) {
            $formsOnPage = IraFormRegistry::findFormsByPath($currentPage, $role);
            if (!empty($formsOnPage)) {
                $formKey = $context['active_form_key'] ?? array_key_first($formsOnPage);
                $formResult = $this->formUnderstanding->processFormTurn(
                    $clean,
                    $formKey,
                    $role,
                    (array) ($context['current_fields'] ?? []),
                    $context
                );

                if (!empty($formResult['form_updates'])
                    || in_array($formResult['status'] ?? '', ['status_query', 'form_reset', 'sensitive_field_blocked', 'file_input_blocked', 'readonly_field'], true)
                ) {
                    return [
                        'success' => true,
                        'intent' => 'form_fill',
                        'destination_key' => null,
                        'target_url' => null,
                        'form_key' => $formResult['form_key'],
                        'form_title' => $formResult['form_title'] ?? '',
                        'form_selector' => $formResult['form_selector'],
                        'form_updates' => $formResult['form_updates'],
                        'remaining_required' => $formResult['remaining_required'] ?? [],
                        'sensitive_skipped' => $formResult['sensitive_skipped'] ?? [],
                        'warnings' => $formResult['warnings'] ?? [],
                        'speech' => $formResult['speech'],
                        'response_language' => $formResult['speech_language'] ?? 'hinglish',
                        'confidence' => 0.95,
                        'needs_clarification' => false,
                        'context' => array_merge($context, [
                            'active_form_key' => $formResult['form_key'],
                            'last_intent' => 'form_fill',
                        ]),
                    ];
                }
            }
        }

        // 3. Dedicated Navigation Gemini Bridge (gemini-3.5-flash-lite)
        if ($this->navGeminiBridge !== null && $this->navGeminiBridge->isConfigured()) {
            $bridgeResult = $this->resolveWithNavigationBridge($clean, $role, $currentPage, $currentMode, $detectedLang, $context);
            if ($bridgeResult !== null) {
                return $bridgeResult;
            }
        } elseif ($this->gemini->isConfigured()) {
            $aiResult = $this->resolveWithGemini($clean, $role, $currentPage, $currentMode, $detectedLang, $context);
            if ($aiResult !== null) {
                return $aiResult;
            }
        }

        // 4. Intelligent, non-repetitive clarification fallback
        return $this->buildSmartFallbackResponse($clean, $role, $currentPage, $detectedLang);
    }

    /**
     * Resolve user response when an immediate clarification or confirmation is pending.
     */
    protected function resolvePendingClarification(string $clean, string $role, string $lang, array $context): ?array
    {
        $pendingType = $context['pending_clarification'] ?? '';
        $normalized = $this->normalizeUtterance($clean);

        // A. Logout Confirmation Follow-up
        if ($pendingType === 'logout_confirm') {
            // Check cancellation first
            if (preg_match('/\b(nahi|no|nope|cancel|mat|mat\s*karo|rehne\s*do|ruk\s*jao|chhoro|stop|dont|don\'t)\b/iu', $clean)
                || preg_match('/(?:नहीं|ना|कैंसिल|मत\s*करो|रहने\s*दो)/u', $clean)) {
                return [
                    'success' => true,
                    'intent' => 'cancel',
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $lang === 'english'
                        ? "Logout cancelled. How else can I assist you with navigation?"
                        : "Theek hai, logout cancel kar diya gaya hai. Main aapki navigation mein kya madad kar sakti hoon?",
                    'response_language' => $lang,
                    'confidence' => 0.95,
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }

            // Confirming logout
            if (preg_match('/\b(haan|ha|yes|yep|sure|proceed|confirm|karna\s*hai|kar\s*do|kardo|haan\s*kardo|logout\s*kar\s*do)\b/iu', $clean)
                || preg_match('/(?:हाँ|हां|हाँ\s*करो|लॉगआउट)/u', $clean)) {
                
                $logoutDest = ($role === 'admin') ? 'admin_logout' : 'member_logout';
                $url = url($this->destinations[$logoutDest]['path']);
                
                return [
                    'success' => true,
                    'intent' => 'navigate',
                    'destination_key' => $logoutDest,
                    'target_url' => $url,
                    'speech' => $lang === 'english'
                        ? "Logging you out securely now. Goodbye!"
                        : "Ji, main aapko safely logout kar rahi hoon. Alvida!",
                    'response_language' => $lang,
                    'confidence' => 0.98,
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }

            // If the user says an unrelated navigation command (e.g. "dashboard kholo"), allow fallthrough
            if (preg_match('/\b(dashboard|wallet|staking|profile|trading|geneology|tickets?)\b/iu', $clean)) {
                // clear pending and let normal resolution continue
                return null;
            }

            // Ambiguous reply to logout confirmation: do NOT log out
            return [
                'success' => true,
                'intent' => 'clarify',
                'destination_key' => null,
                'target_url' => null,
                'speech' => $lang === 'english'
                    ? "Please clearly say 'yes' to log out, or 'no' to stay logged in."
                    : "Kripya spasht batayein: logout karne ke liye 'haan' kahein, ya bane rehne ke liye 'nahi' bolein.",
                'response_language' => $lang,
                'confidence' => 0.9,
                'needs_clarification' => true,
                'context' => ['pending_clarification' => 'logout_confirm'],
            ];
        }

        // B. Cancellation for any selection / options flow (only if NO alternative choice was specified)
        if (in_array($pendingType, ['section_page_choice', 'income_choice', 'wallet_choice', 'staking_choice'], true)) {
            $isExplicitCancel = preg_match('/\b(cancel|rehne\s*do|ruk\s*jao|mat\s*karo|chhod\s*do|chhoro|stop)\b/iu', $clean)
                || preg_match('/(?:कैंसिल|रहने\s*दो|रुक\s*जाओ|मत\s*करो|छोड़ो)/u', $clean);

            if ($isExplicitCancel) {
                return [
                    'success' => true,
                    'intent' => 'cancel',
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $lang === 'english'
                        ? "Action cancelled. What would you like to do instead?"
                        : "Theek hai, cancel kar diya gaya. Main aapki kya madad kar sakti hoon?",
                    'response_language' => $lang,
                    'confidence' => 0.95,
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }

            $hasChoicePayload = preg_match('/\b(pehla|first|1st|ek\s+number|number\s+1|doosra|dusra|second|2nd|do\s+number|number\s+2|teesra|tisra|third|3rd|teen\s+number|chautha|fourth|4th|panchva|fifth|5th|aakhri|last)\b/iu', $clean)
                || preg_match('/(?<!rehne\s|jane\s|mat\s|chhod\s|chhor\s|rok\s|kar\s|khol\s|bata\s|dikha\s)\b(?:ek|do|teen|chaar|paanch)\b/iu', $clean)
                || preg_match('/(?:पहला|फर्स्ट|1st|दूसरा|दुसरा|सेकंड|2nd|तीसरा|थर्ड|3rd|चौथा|फोर्थ|4th|पांचवां|फिफ्थ|5th|आखिरी|लास्ट)/u', $clean);

            if (!$hasChoicePayload && !empty($context['pending_options']) && is_array($context['pending_options'])) {
                foreach ($context['pending_options'] as $optLabel => $optDest) {
                    if (str_contains(mb_strtolower($clean, 'UTF-8'), mb_strtolower($optLabel, 'UTF-8'))) {
                        $hasChoicePayload = true;
                        break;
                    }
                }
            }

            if (!$hasChoicePayload && (preg_match('/\b(nahi|no|nope|cancel|mat|mat\s*karo|rehne\s*do|ruk\s*jao|chhoro|stop|dont|don\'t)\b/iu', $clean)
                || preg_match('/(?:नहीं|ना|कैंसिल|मत\s*करो|रहने\s*दो)/u', $clean))) {
                return [
                    'success' => true,
                    'intent' => 'cancel',
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $lang === 'english'
                        ? "Action cancelled. What would you like to do instead?"
                        : "Theek hai, cancel kar diya gaya. Main aapki kya madad kar sakti hoon?",
                    'response_language' => $lang,
                    'confidence' => 0.95,
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }
        }

        // C. Income Choice Follow-up
        if ($pendingType === 'income_choice') {
            if (preg_match('/\b(direct|referral|direct\s+income|pehla|first)\b/iu', $clean) || preg_match('/(?:डायरेक्ट|पहला)/u', $clean)) {
                $dest = ($role === 'admin') ? 'admin_income_direct' : 'member_income_direct';
                return $this->resolveDestination($dest, $role, $lang);
            }
            if (preg_match('/\b(level|level\s+income|doosra|second)\b/iu', $clean) || preg_match('/(?:लेवल|दूसरा)/u', $clean)) {
                $dest = ($role === 'admin') ? 'admin_income_level' : 'member_income_level';
                return $this->resolveDestination($dest, $role, $lang);
            }
            if (preg_match('/\b(reward|rewards|reward\s+income|teesra|third)\b/iu', $clean) || preg_match('/(?:रिवार्ड|रिवॉर्ड|तीसरा)/u', $clean)) {
                $dest = ($role === 'admin') ? 'admin_income_reward' : 'member_income_reward';
                return $this->resolveDestination($dest, $role, $lang);
            }
            if (preg_match('/\b(staking|staking\s+income|chautha|fourth)\b/iu', $clean) || preg_match('/(?:स्टेकिंग|चौथा)/u', $clean)) {
                $dest = ($role === 'admin') ? 'admin_staking_incomes' : 'member_income_staking';
                return $this->resolveDestination($dest, $role, $lang);
            }
        }

        // D. Wallet Choice Follow-up
        if ($pendingType === 'wallet_choice') {
            if (preg_match('/\b(withdrawal\s+fund|main\s+wallet|withdraw|nikasi|pehla|first)\b/iu', $clean) || preg_match('/(?:विथड्रॉल|निकासी|पहला)/u', $clean)) {
                return $this->resolveDestination('member_wallet_main', $role, $lang);
            }
            if (preg_match('/\b(history|withdrawal\s+history|doosra|second)\b/iu', $clean) || preg_match('/(?:हिस्ट्री|दूसरा)/u', $clean)) {
                return $this->resolveDestination('member_wallet_history', $role, $lang);
            }
            if (preg_match('/\b(p2p|p2p\s+transfer|wallet\s+to\s+p2p|teesra|third)\b/iu', $clean) || preg_match('/(?:पी2पी|तीसरा)/u', $clean)) {
                return $this->resolveDestination('member_wallet_to_p2p', $role, $lang);
            }
            if (preg_match('/\b(transaction|transactions|all\s+transaction|ledger|chautha|fourth)\b/iu', $clean) || preg_match('/(?:ट्रांजेक्शन|चौथा)/u', $clean)) {
                return $this->resolveDestination('member_wallet_transactions', $role, $lang);
            }
        }

        // E. Investment / Staking Choice Follow-up
        if ($pendingType === 'staking_choice') {
            if (preg_match('/\b(create|new|investment|portal|pehla|first)\b/iu', $clean) || preg_match('/(?:क्रिएट|पहला)/u', $clean)) {
                return $this->resolveDestination('member_staking', $role, $lang);
            }
            if (preg_match('/\b(details|history|record|doosra|second)\b/iu', $clean) || preg_match('/(?:डिटेल्स|दूसरा)/u', $clean)) {
                $dest = ($role === 'admin') ? 'admin_staking_details' : 'member_staking_details';
                return $this->resolveDestination($dest, $role, $lang);
            }
        }

        // F. Section Page Choice Follow-up (after Section Discovery)
        if ($pendingType === 'section_page_choice') {
            $cleanLower = mb_strtolower(trim($clean), 'UTF-8');

            // 1. Re-listing pages if user says "saare pages batao" or "list all pages"
            if (preg_match('/\b(?:saare|all|sabhi|sab)\s+pages?\b/iu', $cleanLower)
                || preg_match('/(?:सारे|सभी|सब)\s*पेज/u', $clean)
                || preg_match('/\b(?:is\s+section\s+ke\s+(?:saare\s+)?pages)\b/iu', $cleanLower)) {
                $pendingSec = $context['pending_section'] ?? null;
                $panelKey = ($role === 'admin') ? 'admin' : 'member';
                if ($pendingSec && isset($this->sidebarSections[$panelKey][$pendingSec])) {
                    return $this->buildSectionDiscoveryPayload($pendingSec, $this->sidebarSections[$panelKey][$pendingSec], $lang);
                }
            }

            // 2. Unrelated navigation commands fallthrough to normal resolution
            if (preg_match('/\b(dashboard|logout|trading|geneology)\b/iu', $cleanLower) || preg_match('/(?:डैशबोर्ड|लॉगआउट)/u', $clean)) {
                return null;
            }

            $sectionPages = $context['pending_options'] ?? [];
            if (!empty($sectionPages) && is_array($sectionPages)) {
                $pageKeys = array_values($sectionPages);
                $pageLabels = array_keys($sectionPages);
                $totalOptions = count($pageKeys);

                // 3. Ordinal matching (pehla/1st, doosra/2nd, teesra/3rd, etc.)
                $selectedIndex = null;
                if (preg_match('/\b(?:pehla|first|1st)\b/iu', $cleanLower) || preg_match('/(?<!kar\s)\b(?:ek)\b/iu', $cleanLower) || preg_match('/(?:पहला|फर्स्ट|1st)/u', $clean)) {
                    $selectedIndex = 0;
                } elseif (preg_match('/\b(?:doosra|dusra|second|2nd|do\s+number|number\s+2)\b/iu', $cleanLower) || preg_match('/(?<!kar\s|khol\s|bata\s|dikha\s)\b(?:do)\b/iu', $cleanLower) || preg_match('/(?:दूसरा|दुसरा|सेकंड|2nd)/u', $clean)) {
                    $selectedIndex = 1;
                } elseif (preg_match('/\b(?:teesra|tisra|third|3rd|teen)\b/iu', $cleanLower) || preg_match('/(?:तीसरा|थर्ड|3rd)/u', $clean)) {
                    $selectedIndex = 2;
                } elseif (preg_match('/\b(?:chautha|fourth|4th|chaar)\b/iu', $cleanLower) || preg_match('/(?:चौथा|फोर्थ|4th)/u', $clean)) {
                    $selectedIndex = 3;
                } elseif (preg_match('/\b(?:panchva|fifth|5th|paanch)\b/iu', $cleanLower) || preg_match('/(?:पांचवां|फिफ्थ|5th)/u', $clean)) {
                    $selectedIndex = 4;
                } elseif (preg_match('/\b(?:aakhri|last)\b/iu', $cleanLower) || preg_match('/(?:आखिरी|लास्ट)/u', $clean)) {
                    $selectedIndex = $totalOptions - 1;
                }

                if ($selectedIndex !== null && isset($pageKeys[$selectedIndex])) {
                    return $this->resolveDestination($pageKeys[$selectedIndex], $role, $lang);
                }

                // 4. Exact child label match
                foreach ($sectionPages as $label => $destKey) {
                    if (str_contains($cleanLower, mb_strtolower($label, 'UTF-8'))) {
                        return $this->resolveDestination($destKey, $role, $lang);
                    }
                }

                // 5. Distinctive keyword match in pending options
                $stopWords = ['page', 'pages', 'wala', 'wali', 'wale', 'par', 'pe', 'kholo', 'dikhao', 'section', 'mein', 'in', 'open', 'show', 'the', 'le', 'chalo', 'jao', 'karo', 'kar', 'do', 'please', 'mujhe', 'khol', 'nahi'];
                $cleanTokens = array_diff(preg_split('/\s+/u', $cleanLower), $stopWords);

                $bestDest = null;
                $maxTokenMatches = 0;

                foreach ($sectionPages as $label => $destKey) {
                    $labelTokens = array_diff(preg_split('/\s+/u', mb_strtolower($label, 'UTF-8')), $stopWords);
                    $matches = count(array_intersect($cleanTokens, $labelTokens));
                    if ($matches > $maxTokenMatches) {
                        $maxTokenMatches = $matches;
                        $bestDest = $destKey;
                    }
                }

                if ($bestDest !== null && $maxTokenMatches > 0) {
                    return $this->resolveDestination($bestDest, $role, $lang);
                }
            }
        }

        return null;
    }

    /**
     * Deterministic, high-confidence navigation and section discovery resolver.
     */
    public function resolveDeterministic(
        string $clean,
        string $role,
        string $currentPage,
        string $lang,
        array $context
    ): ?array {
        $normalized = $this->normalizeUtterance($clean);

        // ============================================================
        // 1. Core Window Controls & Browser History
        // ============================================================

        // Close Assistant
        if (preg_match('/\b(window\s+band|band\s+(?:kar\s+do|karo|kijiye|kardo)|close\s+(?:window|assistant|panel|modal|this)|ye\s+band\s+(?:kar|karo)|chupa\s+do|hata\s+do|dismiss)\b/iu', $clean)
            || preg_match('/(?:विंडो\s*बंद|असिस्टेंट\s*बंद|बंद\s*(?:करो|कर\s*दो|कीजिए|करें)|हटा\s*दो|छुपा\s*दो|बंद)/u', $clean)) {
            return [
                'success' => true,
                'intent' => 'close_assistant',
                'destination_key' => null,
                'target_url' => null,
                'speech' => $lang === 'english' ? "Closing the IRA assistant window." : "Ji, main assistant window band kar rahi hoon.",
                'response_language' => $lang,
                'confidence' => 0.99,
                'needs_clarification' => false,
                'context' => [],
            ];
        }

        // Go Back / Previous Page
        if (preg_match('/\b(pichhe\s+jao|peechhe\s+jao|wapas\s+chalo|go\s+back|previous\s+page|pichhla\s+page|pichle\s+page|pichhle\s+page|wapas\s+le\s+chalo|back\s+jao|back\s+chalo|take\s+me\s+back|pichle\s+page\s+par)\b/iu', $clean)
            || preg_match('/(?:पीछे\s*(?:जाओ|चलो)|वापस\s*(?:चलो|जाओ|ले\s*चलो)|पिछला\s*पेज|पिछले\s*पेज)/u', $clean)) {
            return [
                'success' => true,
                'intent' => 'go_back',
                'destination_key' => null,
                'target_url' => null,
                'speech' => $lang === 'english' ? "Navigating back to the previous page." : "Pichhle page par wapas chalte hain.",
                'response_language' => $lang,
                'confidence' => 0.98,
                'needs_clarification' => false,
                'context' => [],
            ];
        }

        // Go Forward / Next Page
        if (preg_match('/\b(aage\s+wale\s+page\s+par\s+jao|aage\s+jao|aage\s+chalo|agla\s+page|agle\s+page\s+par|go\s+forward|forward\s+jao|forward\s+chalo|next\s+page)\b/iu', $clean)
            || preg_match('/(?:आगे\s*(?:जाओ|चलो|वाले\s*पेज)|अगला\s*पेज|अगले\s*पेज)/u', $clean)) {
            return [
                'success' => true,
                'intent' => 'go_forward',
                'destination_key' => null,
                'target_url' => null,
                'speech' => $lang === 'english' ? "Navigating forward to the next page." : "Aage wale page par chalte hain.",
                'response_language' => $lang,
                'confidence' => 0.98,
                'needs_clarification' => false,
                'context' => [],
            ];
        }

        // ============================================================
        // 2. Secure Logout Flow Initiation
        // ============================================================
        if (preg_match('/\b(logout|log\s+out|log\s+me\s+out|sign\s+out|logout\s+karna\s+hai|logout\s+kar\s+do|logout\s+karo)\b/iu', $clean)
            || preg_match('/(?:लॉगआउट|साइन\s*आउट)/u', $clean)) {
            
            if ($role === 'guest') {
                return [
                    'success' => true,
                    'intent' => 'general_question',
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $lang === 'english'
                        ? "You are not currently logged in."
                        : "Aap abhi logged in nahi hain.",
                    'response_language' => $lang,
                    'confidence' => 0.95,
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }

            return [
                'success' => true,
                'intent' => 'logout_prompt',
                'destination_key' => null,
                'target_url' => null,
                'speech' => $lang === 'english'
                    ? "Are you sure you want to log out? Please say yes or no."
                    : "Kya aap sach mein logout karna chahte hain? Haan ya nahi boliye.",
                'response_language' => $lang,
                'confidence' => 0.95,
                'needs_clarification' => true,
                'context' => ['pending_clarification' => 'logout_confirm'],
            ];
        }

        // ============================================================
        // 3. Section Discovery ("X section mein kaun-kaun se pages hain?")
        // ============================================================
        $discoveryResult = $this->resolveSectionDiscovery($clean, $normalized, $role, $lang, $currentPage);
        if ($discoveryResult !== null) {
            return $discoveryResult;
        }

        // ============================================================
        // 4. Context Page Explanation ("Ye kya hai", "Explain this page")
        // ============================================================
        if (preg_match('/\b(?:ye\s+kya\s+hai|simple\s+(?:bhasha\s+mein\s+)?samjhao|explain\s+this\s+page|is\s+page\s+par\s+kya\s+hota\s+hai|is\s+page\s+pe\s+kya\s+karna\s+hai)\b/iu', $clean)
            || preg_match('/(?:ये\s*क्या\s*है|सिंपल\s*भाषा\s*में\s*समझाइए|इस\s*पेज\s*पर\s*क्या\s*है|समझाओ|समझाइए)/u', $clean)) {
            return $this->buildPageExplanation($currentPage, $role, $lang);
        }

        // ============================================================
        // 5. Cross-Role Access Checks (Explicit Denials / Guidance)
        // ============================================================

        // Member requesting Admin Dashboard
        if (preg_match('/\badmin\s+(?:dashboard|panel|portal)\b/iu', $clean) || preg_match('/एडमिन\s*(?:डैशबोर्ड|पैनल)/u', $clean)) {
            if ($role === 'member') {
                return [
                    'success' => false,
                    'intent' => 'unauthorized',
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $lang === 'english'
                        ? "You are logged in as a member. The Admin Dashboard requires administrator privileges."
                        : "Aap member account se logged in hain. Admin dashboard access karne ke liye administrator credentials zaroori hain.",
                    'response_language' => $lang,
                    'confidence' => 0.98,
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }
            return $this->resolveDestination('admin_dashboard', $role, $lang);
        }

        // Member requesting Member Details (an Admin-Only section)
        if (preg_match('/\bmember\s+details\b/iu', $clean) || preg_match('/मेंबर\s*डिटेल्स/u', $clean)) {
            if ($role === 'member') {
                return [
                    'success' => false,
                    'intent' => 'unauthorized',
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $lang === 'english'
                        ? "The Member Details management page is restricted to administrators only. It cannot be accessed from a member account."
                        : "Member Details page Admin portal ka part hai. Member account se yeh access nahi kiya ja sakta.",
                    'response_language' => $lang,
                    'confidence' => 0.98,
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }
            if ($role === 'admin') {
                return $this->buildSectionDiscoveryPayload('member_management', $this->sidebarSections['admin']['member_management'], $lang);
            }
            return $this->resolveDestination('admin_login', $role, $lang);
        }

        // Member requesting Admin Member Management
        if (preg_match('/\bmember\s+management\b/iu', $clean) || preg_match('/मेंबर\s*मैनेजमेंट/u', $clean)) {
            if ($role === 'member') {
                return [
                    'success' => false,
                    'intent' => 'unauthorized',
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $lang === 'english'
                        ? "Member Management is an Administrator-only section."
                        : "Member Management Admin portal ka section hai. Member account se yeh access nahi kiya ja sakta.",
                    'response_language' => $lang,
                    'confidence' => 0.98,
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }
            if ($role === 'admin') {
                return $this->buildSectionDiscoveryPayload('member_management', $this->sidebarSections['admin']['member_management'], $lang);
            }
        }

        // ============================================================
        // 6. Clarification for Ambiguous Commands
        // ============================================================

        // Ambiguous Income Command ("Income page par le chalo", "Income dikhao")
        $isAmbiguousIncome = preg_match('/\b(?:income|earnings?|kamai)\b/iu', $clean)
            && !preg_match('/\b(direct|level|reward|staking|roi)\b/iu', $clean)
            && !preg_match('/(?:डायरेक्ट|लेवल|रिवार्ड|स्टेकिंग)/u', $clean);

        if ($isAmbiguousIncome) {
            if ($role === 'guest') {
                return $this->resolveDestination('member_income_direct', $role, $lang);
            }

            if ($role === 'admin') {
                return [
                    'success' => true,
                    'intent' => 'clarify',
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $lang === 'english'
                        ? "Which income section would you like to open: Level Income, Reward Income, or Direct Income?"
                        : "Admin panel mein kaunsi income dekhni hai — Level Income, Reward Income ya Direct Income?",
                    'response_language' => $lang,
                    'confidence' => 0.92,
                    'needs_clarification' => true,
                    'options' => ['Level Incomes', 'Reward Income', 'Direct Income'],
                    'context' => ['pending_clarification' => 'income_choice'],
                ];
            }

            return [
                'success' => true,
                'intent' => 'clarify',
                'destination_key' => null,
                'target_url' => null,
                'speech' => $lang === 'english'
                    ? "Which income report would you like to see: Direct Income, Level Income, Staking Income, or Reward Income?"
                    : "Kaunsi income dekhni hai — Direct Income, Level Income, Staking Income ya Reward Income?",
                'response_language' => $lang,
                'confidence' => 0.92,
                'needs_clarification' => true,
                'options' => ['Direct Income', 'Level Income', 'Staking Income', 'Reward Income'],
                'context' => ['pending_clarification' => 'income_choice'],
            ];
        }

        // Ambiguous Wallet Command ("Wallet wala page khol do", "Wallet dikhao")
        $isAmbiguousWallet = preg_match('/\b(?:wallet|wallets)\b/iu', $clean)
            && !preg_match('/\b(history|to\s*p2p|p2p|all\s*transaction|address|member\s+wallet)\b/iu', $clean)
            && !preg_match('/(?:हिस्ट्री|पी2पी|ट्रांजेक्शन|एड्रेस)/u', $clean);

        if ($isAmbiguousWallet) {
            if ($role === 'guest') {
                return $this->resolveDestination('member_wallet_main', $role, $lang);
            }

            if ($role === 'admin') {
                return $this->resolveDestination('admin_wallet_address', $role, $lang);
            }

            return [
                'success' => true,
                'intent' => 'clarify',
                'destination_key' => null,
                'target_url' => null,
                'speech' => $lang === 'english'
                    ? "Which wallet page would you like to open: Withdrawal Fund, Withdrawal History, Wallet to P2P Transfer, or Transaction History?"
                    : "Kaun sa wallet page kholna hai — Withdrawal Fund, Withdrawal History, Wallet to P2P Transfer ya Transaction History?",
                'response_language' => $lang,
                'confidence' => 0.92,
                'needs_clarification' => true,
                'options' => ['Withdrawal Fund', 'Withdrawal History', 'Wallet to P2P Transfer', 'Transaction History'],
                'context' => ['pending_clarification' => 'wallet_choice'],
            ];
        }

        // Ambiguous Staking / Investment Command
        $isAmbiguousStaking = preg_match('/\b(?:staking|investment)\b/iu', $clean)
            && !preg_match('/\b(income|details|history|create|new)\b/iu', $clean)
            && !preg_match('/(?:इनकम|डिटेल्स|क्रिएट)/u', $clean);

        if ($isAmbiguousStaking) {
            if ($role === 'guest') {
                return $this->resolveDestination('member_staking', $role, $lang);
            }

            if ($role === 'admin') {
                return $this->resolveDestination('admin_staking_details', $role, $lang);
            }

            return [
                'success' => true,
                'intent' => 'clarify',
                'destination_key' => null,
                'target_url' => null,
                'speech' => $lang === 'english'
                    ? "Would you like to Create a new Investment or view your Investment Details?"
                    : "Aap naya Investment create karna chahte hain ya Investment Details dekhni hain?",
                'response_language' => $lang,
                'confidence' => 0.92,
                'needs_clarification' => true,
                'options' => ['Create Investment', 'Investment Details'],
                'context' => ['pending_clarification' => 'staking_choice'],
            ];
        }

        // ============================================================
        // 7. Direct Child Page & Panel-Scoped Disambiguation
        // ============================================================

        // Specific Registered Child Page Navigation via Ontology (Longest Alias Match First)
        $panelKey = ($role === 'admin') ? 'admin' : 'member';
        $pageMatch = AppNavigationOntology::findPageByAlias($clean, $panelKey);
        if ($pageMatch !== null) {
            if (($pageMatch['key'] === 'member_dashboard' || $pageMatch['key'] === 'admin_dashboard') && preg_match('/\b(?:messages?|image|popup)\b/iu', $clean)) {
                // allow admin dashboard messages or popup image to be resolved specifically
            } else {
                return $this->resolveDestination($pageMatch['key'], $role, $lang);
            }
        }

        // Dashboard (with typo tolerance)
        if ((preg_match('/\b(?:dashboard|dash\s*board|main\s+dashboard|dashbord|member\s+dashbord)\b/iu', $clean) || preg_match('/(?:डैशबोर्ड)/u', $clean))
            && !preg_match('/\b(?:messages?|image|popup)\b/iu', $clean)
            && !preg_match('/(?:मैसेज|इमेज|पॉपअप)/u', $clean)) {
            $dest = ($role === 'admin') ? 'admin_dashboard' : 'member_dashboard';
            return $this->resolveDestination($dest, $role, $lang);
        }

        // Profile (with typo tolerance)
        if (preg_match('/\b(?:profile|my\s+profile|meri\s+profile|edit\s+profile|profiel|meri\s+profiel|my\s+profiel)\b/iu', $clean)
            || preg_match('/(?:प्रोफाइल|मेरी\s*प्रोफाइल)/u', $clean)) {
            $dest = ($role === 'admin') ? 'admin_profile' : 'member_profile_summary';
            return $this->resolveDestination($dest, $role, $lang);
        }

        // Public Frontend / Website Home
        if (preg_match('/\b(?:frontend|public\s+home|main\s+website|website\s+kholo|front\s*end|home\s+page)\b/iu', $clean)
            || preg_match('/(?:वेबसाइट|होम\s*पेज|फ्रंटएंड)/u', $clean)) {
            return $this->resolveDestination('home', $role, $lang);
        }

        // Public Gallery
        if (preg_match('/\b(?:gallery|photo\s+gallery|public\s+gallery)\b/iu', $clean) || preg_match('/गैलरी/u', $clean)) {
            return $this->resolveDestination('gallery', $role, $lang);
        }

        // Admin Login
        if (preg_match('/\b(?:admin\s+login|admin\s+sign\s*in)\b/iu', $clean) || preg_match('/एडमिन\s*लॉगिन/u', $clean)) {
            return $this->resolveDestination('admin_login', $role, $lang);
        }

        // Member Login
        if (preg_match('/\b(?:member\s+login|member\s+sign\s*in|user\s+login)\b/iu', $clean) || preg_match('/मेंबर\s*लॉगिन/u', $clean)) {
            return $this->resolveDestination('member_login', $role, $lang);
        }

        // Generic Login (scope to guest/public login)
        if (preg_match('/\b(?:login\s+page|sign\s*in\s+page|login\s+khol\s*do)\b/iu', $clean) || preg_match('/लॉगिन\s*पेज/u', $clean)) {
            return $this->resolveDestination('member_login', $role, $lang);
        }

        // ============================================================
        // 8. Specific Sidebar Item Matching
        // ============================================================

        // Member Profile Submenu
        if (preg_match('/\b(?:change\s+password|security\s+settings|password\s+change|password\s+badlo)\b/iu', $clean)
            || preg_match('/(?:पासवर्ड\s*बदलो|सिक्योरिटी)/u', $clean)) {
            return $this->resolveDestination('member_profile_security', $role, $lang);
        }
        if (preg_match('/\b(?:settings\s*\(?wallet\s+address\)?|wallet\s+address\s+settings|my\s+wallet\s+address)\b/iu', $clean)
            || preg_match('/(?:वॉलेट\s*एड्रेस\s*सेटिंग्स)/u', $clean)) {
            return $this->resolveDestination('member_profile_wallet', $role, $lang);
        }

        // Member Add Fund Submenu
        if (preg_match('/\b(?:add\s+fund\s+history|fund\s+history|deposit\s+history)\b/iu', $clean)
            || preg_match('/(?:फंड\s*हिस्ट्री)/u', $clean)) {
            return $this->resolveDestination('member_fund_history', $role, $lang);
        }
        if (preg_match('/\b(?:add\s+fund|import\s+fund|deposit\s+fund)\b/iu', $clean)
            || preg_match('/(?:फंड\s*ऐड|डिपॉजिट)/u', $clean)) {
            return $this->resolveDestination('member_fund_import', $role, $lang);
        }

        // Member Investment Submenu
        if (preg_match('/\b(?:create\s+investment|new\s+investment|investment\s+karo)\b/iu', $clean)
            || preg_match('/(?:क्रिएट\s*इन्वेस्टमेंट)/u', $clean)) {
            return $this->resolveDestination('member_staking', $role, $lang);
        }
        if (preg_match('/\b(?:investment\s+details|meri\s+investment|my\s+investments?)\b/iu', $clean)
            || preg_match('/(?:इन्वेस्टमेंट\s*डिटेल्स)/u', $clean)) {
            return $this->resolveDestination('member_staking_details', $role, $lang);
        }

        // Member Earnings / Income Submenu
        if (preg_match('/\b(?:staking\s+income|stake\s+income)\b/iu', $clean) || preg_match('/स्टेकिंग\s*इनकम/u', $clean)) {
            return $this->resolveDestination('member_income_staking', $role, $lang);
        }
        if (preg_match('/\b(?:level\s+income|levels\s+income)\b/iu', $clean) || preg_match('/लेवल\s*इनकम/u', $clean)) {
            $dest = ($role === 'admin') ? 'admin_income_level' : 'member_income_level';
            return $this->resolveDestination($dest, $role, $lang);
        }
        if (preg_match('/\b(?:direct\s+income|referral\s+income)\b/iu', $clean) || preg_match('/डायरेक्ट\s*इनकम/u', $clean)) {
            $dest = ($role === 'admin') ? 'admin_income_direct' : 'member_income_direct';
            return $this->resolveDestination($dest, $role, $lang);
        }

        // Member Reward Submenu
        if (preg_match('/\b(?:reward\s+details|my\s+rewards)\b/iu', $clean) || preg_match('/रिवार्ड\s*डिटेल्स/u', $clean)) {
            return $this->resolveDestination('member_income_reward_details', $role, $lang);
        }
        if (preg_match('/\b(?:reward\s+income|rewards?\s+income)\b/iu', $clean) || preg_match('/रिवार्ड\s*इनकम/u', $clean)) {
            $dest = ($role === 'admin') ? 'admin_income_reward' : 'member_income_reward';
            return $this->resolveDestination($dest, $role, $lang);
        }

        // Member Team Management Submenu
        if (preg_match('/\b(?:geneology|genealogy|team\s+tree|network\s+tree)\b/iu', $clean)
            || preg_match('/(?:जीनियोलॉजी|टीम\s*ट्री)/u', $clean)) {
            return $this->resolveDestination('member_team_geneology', $role, $lang);
        }
        if (preg_match('/\b(?:direct\s+members|my\s+directs|direct\s+team)\b/iu', $clean)
            || preg_match('/(?:डायरेक्ट\s*मेंबर्स)/u', $clean)) {
            return $this->resolveDestination('member_team_direct', $role, $lang);
        }
        if (preg_match('/\b(?:level\s+members?\s+table|level\s+members|level\s+table)\b/iu', $clean)
            || preg_match('/(?:लेवल\s*मेंबर\s*टेबल)/u', $clean)) {
            return $this->resolveDestination('member_team_level_table', $role, $lang);
        }

        // Member Withdrawal & Wallet Submenu
        if (preg_match('/\b(?:withdrawal\s+fund|withdraw\s+fund|nikasi\s+fund)\b/iu', $clean)
            || preg_match('/(?:विथड्रॉल\s*फंड|निकासी)/u', $clean)) {
            return $this->resolveDestination('member_wallet_main', $role, $lang);
        }
        if (preg_match('/\b(?:withdrawal\s+history|meri\s+withdrawals?)\b/iu', $clean)
            || preg_match('/(?:विथड्रॉल\s*हिस्ट्री)/u', $clean)) {
            return $this->resolveDestination('member_wallet_history', $role, $lang);
        }
        if (preg_match('/\b(?:wallet\s+to\s+p2p(?:\s+transfer)?)\b/iu', $clean)
            || preg_match('/(?:वॉलेट\s*टू\s*पी2पी)/u', $clean)) {
            return $this->resolveDestination('member_wallet_to_p2p', $role, $lang);
        }
        if (preg_match('/\b(?:transaction\s+history|all\s+transactions?|transactions?\s+ledger)\b/iu', $clean)
            || preg_match('/(?:ट्रांजेक्शन\s*हिस्ट्री|लेजर)/u', $clean)) {
            return $this->resolveDestination('member_wallet_transactions', $role, $lang);
        }

        // Member P2P Submenu
        if (preg_match('/\b(?:p2p\s+history|p\s+to\s+p\s+history|petrol\s+history|p2p\s+wallet\s+history)\b/iu', $clean) || preg_match('/(?:पी2पी|पी\s*टू\s*पी)\s*हिस्ट्री/u', $clean)) {
            return $this->resolveDestination('member_p2p_history', $role, $lang);
        }
        if (preg_match('/\b(?:p2p\s+wallet|p\s+to\s+p\s+wallet|petrol\s+wallet|p2p\s+transfer|p2p|p\s+to\s+p|petrol)\b/iu', $clean) || preg_match('/(?:पी2पी|पी\s*टू\s*पी)\s*वॉलेट/u', $clean)) {
            return $this->resolveDestination('member_p2p_wallet', $role, $lang);
        }

        // Member Support Submenu
        if (preg_match('/\b(?:create\s+(?:new\s+)?ticket|new\s+ticket|raise\s+ticket)\b/iu', $clean)
            || preg_match('/(?:नया\s*टिकट|टिकट\s*बनाओ)/u', $clean)) {
            return $this->resolveDestination('member_support_create', $role, $lang);
        }
        if (preg_match('/\b(?:support\s+tickets?|my\s+tickets?)\b/iu', $clean)
            || preg_match('/(?:सपोर्ट\s*टिकट्स)/u', $clean)) {
            return $this->resolveDestination('member_support_tickets', $role, $lang);
        }

        // Arbitrage Trading
        if (preg_match('/\b(?:arbitrage|trading|arbitrage\s+trading)\b/iu', $clean) || preg_match('/(?:आर्बिट्राज|ट्रेडिंग)/u', $clean)) {
            return $this->resolveDestination('member_trading', $role, $lang);
        }

        // Admin Specific Submenu Items
        if ($role === 'admin') {
            if (preg_match('/\b(?:popup\s+image|dashboard\s+image|banners?)\b/iu', $clean)) {
                return $this->resolveDestination('admin_popup_image', $role, $lang);
            }
            if (preg_match('/\b(?:dashboard\s+messages?|dash\s+messages?)\b/iu', $clean)) {
                return $this->resolveDestination('admin_dash_messages', $role, $lang);
            }
            if (preg_match('/\b(?:account\s+control|user\s+status)\b/iu', $clean)) {
                return $this->resolveDestination('admin_account_control', $role, $lang);
            }
            if (preg_match('/\b(?:members?\s+security|user\s+security)\b/iu', $clean)) {
                return $this->resolveDestination('admin_member_security', $role, $lang);
            }
            if (preg_match('/\b(?:zero\s+pin\s+members?|free\s+pin\s+account)\b/iu', $clean)) {
                return $this->resolveDestination('admin_free_pin_account', $role, $lang);
            }
            if (preg_match('/\b(?:member\s+wallet\s+address|users?\s+wallet\s+address)\b/iu', $clean)) {
                return $this->resolveDestination('admin_wallet_address', $role, $lang);
            }
            if (preg_match('/\b(?:add\s+funds?)\b/iu', $clean)) {
                return $this->resolveDestination('admin_funds_add', $role, $lang);
            }
            if (preg_match('/\b(?:deduct\s+funds?)\b/iu', $clean)) {
                return $this->resolveDestination('admin_funds_deduct', $role, $lang);
            }
            if (preg_match('/\b(?:funds?\s+details)\b/iu', $clean)) {
                return $this->resolveDestination('admin_funds_add_details', $role, $lang);
            }
            if (preg_match('/\b(?:import\s+fund\s+history|request\s+history)\b/iu', $clean)) {
                return $this->resolveDestination('admin_request_history', $role, $lang);
            }
            if (preg_match('/\b(?:admin\s+staking\s+details|staking\s+details)\b/iu', $clean)) {
                return $this->resolveDestination('admin_staking_details', $role, $lang);
            }
            if (preg_match('/\b(?:new\s+withdrawal\s+requests?|pending\s+withdrawals?)\b/iu', $clean)) {
                return $this->resolveDestination('admin_new_withdrawals', $role, $lang);
            }
            if (preg_match('/\b(?:cancelled\s+requests?)\b/iu', $clean)) {
                return $this->resolveDestination('admin_cancelled_requests', $role, $lang);
            }
            if (preg_match('/\b(?:payment\s+history)\b/iu', $clean)) {
                return $this->resolveDestination('admin_payment_history', $role, $lang);
            }
            if (preg_match('/\b(?:new\s+support\s+tickets?)\b/iu', $clean)) {
                return $this->resolveDestination('admin_support_new', $role, $lang);
            }
            if (preg_match('/\b(?:open\s+support\s+tickets?)\b/iu', $clean)) {
                return $this->resolveDestination('admin_support_open', $role, $lang);
            }
            if (preg_match('/\b(?:closed?\s+support\s+tickets?)\b/iu', $clean)) {
                return $this->resolveDestination('admin_support_close', $role, $lang);
            }
        }

        return null;
    }

    /**
     * Resolve questions about the actual application navigation structure (Section Discovery).
     */
    protected function resolveSectionDiscovery(
        string $raw,
        string $normalized,
        string $role,
        string $lang,
        string $currentPage = ''
    ): ?array {
        $clean = mb_strtolower(trim($raw), 'UTF-8');
        $panelKey = ($role === 'admin') ? 'admin' : 'member';
        $otherPanelKey = ($role === 'admin') ? 'member' : 'admin';

        // 1. Check for Contextual Section reference ("is section ke andar kya hai", "is section ke pages batao", "this section")
        $isContextualThisSection = preg_match('/\b(?:is|iss|this)\s+section\b/iu', $clean)
            || preg_match('/(?:इस\s*सेक्शन|इस\s*सेक्शन\s*के)/u', $raw);

        if ($isContextualThisSection && $currentPage !== '') {
            $contextSecKey = $this->getSectionKeyForPage($currentPage, $role);
            if ($contextSecKey !== null) {
                if (isset($this->sidebarSections[$panelKey][$contextSecKey])) {
                    $matchedSecData = $this->sidebarSections[$panelKey][$contextSecKey];
                    return $this->buildSectionDiscoveryPayload($contextSecKey, $matchedSecData, $lang);
                }
            }
        }

        // 2. Comprehensive pattern for questions inquiring about section pages, contents, or details
        $isSectionQuestion = preg_match('/\b(?:'
            . 'ke\s+baare\s+mein\s+batao|ke\s+bare\s+me\s+batao|ke\s+baare\s+mein|ke\s+bare\s+me|baare\s+mein|bare\s+me|'
            . 'batao|bataiye|samjhao|explain|tell\s+me\s+about|about|'
            . 'ke\s+andar\s+kya[-\s]*kya\s+hai|ke\s+andar\s+kya\s+hai|ke\s+andar|ke\s+under|andar\s+kya\s+hai|'
            . 'kya[-\s]*kya\s+pages\s+hain|kya\s+pages\s+hain|kya[-\s]*kya\s+available\s+hai|kya\s+available\s+hai|kya[-\s]*kya\s+hai|kya\s+hai|'
            . 'mein\s+kaun[-\s]*(?:kaun\s*)?se\s+pages?|kaun[-\s]*(?:kaun\s*)?se\s+pages?|kaunse\s+pages?|kaun\s+se\s+pages?|'
            . 'kitne\s+pages?\s+hain|kitne\s+pages?|kaun[-\s]*kaun\s+se\s+options|'
            . 'ke\s+pages?\s+batao|ke\s+pages?\s+dikhao|ke\s+saare\s+pages?|ke\s+all\s+pages?|pages?\s+batao|pages?\s+dikhao|pages?\s+list|'
            . 'what\s+pages\s+are\s+(?:in|under)|which\s+pages\s+are\s+(?:in|under)|what\s+is\s+(?:in|under|inside)|'
            . 'list\s+(?:all\s+)?pages\s+(?:in|under|of)|show\s+(?:all\s+)?pages\s+(?:in|under|of)|show\s+me\s+(?:all\s+)?(?:the\s+)?pages|'
            . 'pages\s+(?:in|under|of)|how\s+many\s+pages\s+(?:in|under)|what\s+are\s+the\s+pages'
            . ')\b/iu', $clean)
            || preg_match('/(?:के\s*बारे\s*में\s*बताओ|के\s*बारे\s*में|क्या[-\s]*(?:क्या\s*)?है|कौन[-\s]*(?:कौन\s*)?से\s*पेज|कितने\s*पेज|पेज\s*(?:बताओ|दिखाओ|हैं)|अंदर\s*क्या\s*है|सारे\s*पेज)/u', $raw)
            || (preg_match('/\b(?:section|management)\b/iu', $clean) && preg_match('/\b(?:pages?|kya|kaun|which|what|list|show|batao|dikhao)\b/iu', $clean))
            || (preg_match('/(?:सेक्शन|मैनेजमेंट)/u', $raw) && preg_match('/(?:पेज|क्या|कौन|लिस्ट|बताओ|दिखाओ)/u', $raw));

        // 3. Scan authorized panel sections and other panel sections using Unicode-safe ontology matching
        $match = AppNavigationOntology::findSectionByAlias($raw, $panelKey);
        $otherMatch = AppNavigationOntology::findSectionByAlias($raw, $otherPanelKey);
        $thisPanelPageMatch = AppNavigationOntology::findPageByAlias($raw, $panelKey);
        $otherPageMatch = AppNavigationOntology::findPageByAlias($raw, $otherPanelKey);

        // 3b. When the user is NOT asking an exploratory section question (e.g. "member dashboard kholo", "mera profile open karo", "withdrawl page kholo"):
        // Prioritize the authorized direct page match over section discovery UNLESS section match was strictly longer/more specific (e.g. "open the withdrawal section")
        if (!$isSectionQuestion && $thisPanelPageMatch !== null && ($match === null || $thisPanelPageMatch['match_length'] >= $match['match_length'])) {
            if ($otherPageMatch !== null && $otherPageMatch['match_length'] > $thisPanelPageMatch['match_length']) {
                return $this->resolveDestination($otherPageMatch['key'], $role, $lang);
            }
            return $this->resolveDestination($thisPanelPageMatch['key'], $role, $lang);
        }

        // 3c. If other panel has a page match (e.g. 'Account Control') and this panel had none
        if (!$isSectionQuestion && $otherPageMatch !== null) {
            return $this->resolveDestination($otherPageMatch['key'], $role, $lang);
        }

        // 4. If the other panel has a strictly longer/better match (e.g. 'Funds Management' (16) vs 'Funds' (5)),
        // or if authorized panel had no match at all, evaluate other panel section
        if ($otherMatch !== null && ($match === null || $otherMatch['match_length'] > ($match['match_length'] ?? 0))) {
            if ($role === 'member') {
                return [
                    'success' => false,
                    'intent' => 'unauthorized',
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $lang === 'english'
                        ? "{$otherMatch['data']['name']} is an Administrator-only section. It cannot be accessed from a member account."
                        : "{$otherMatch['data']['name']} Admin portal ka section hai. Member account se yeh access nahi kiya ja sakta.",
                    'response_language' => $lang,
                    'confidence' => 0.98,
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }
        }

        if ($match !== null) {
            $secKey = $match['key'];
            $secData = $match['data'];

            // 5. Check if the user utterance ALSO specifies a child page of this section!
            // e.g. "Member Management mein Members Details kholo" OR "Earnings section mein Direct Income kholo"
            foreach ($secData['pages'] as $childLabel => $childDestKey) {
                $childLower = mb_strtolower($childLabel, 'UTF-8');
                if ($childLower === mb_strtolower($secData['name'], 'UTF-8') && $isSectionQuestion) {
                    continue;
                }
                if (str_contains($clean, $childLower)) {
                    return $this->resolveDestination($childDestKey, $role, $lang);
                }
            }

            // 6. If it's a section question OR invoking the parent section name directly (since parent has no separate landing page)
            if ($isSectionQuestion
                || preg_match('/\b(?:kholo|open|dikhao|chalo|jao|dekho|details?|section)\b/iu', $clean)
                || preg_match('/(?:खोलो|ओपन|दिखाओ|चलो|जाओ|देखो|सेक्शन)/u', $raw)
                || $match['match_length'] >= mb_strlen($clean, 'UTF-8') - 4) {
                return $this->buildSectionDiscoveryPayload($secKey, $secData, $lang);
            }
        }

        return null;
    }

    /**
     * Map a route/URL path to its parent section key.
     */
    protected function getSectionKeyForPage(string $currentPage, string $role): ?string
    {
        $cleanPath = rtrim(explode('?', $currentPage)[0], '/');
        $panelKey = ($role === 'admin') ? 'admin' : 'member';

        // Direct matching against destinations
        foreach ($this->destinations as $destKey => $dest) {
            if ($dest['panel'] !== $panelKey) {
                continue;
            }
            $destPath = rtrim(explode('?', $dest['path'])[0], '/');
            if ($destPath === $cleanPath || str_ends_with($cleanPath, $destPath)) {
                $sec = $dest['section'] ?? null;
                if ($sec && isset($this->sidebarSections[$panelKey][$sec])) {
                    return $sec;
                }
            }
        }

        // Substring / path segment fallback
        if (str_contains($cleanPath, 'income')) {
            return ($panelKey === 'admin') ? 'income_section' : 'earnings';
        }
        if (str_contains($cleanPath, 'wallet') || str_contains($cleanPath, 'withdrawal')) {
            return ($panelKey === 'admin') ? 'payment_management' : 'withdrawal_section';
        }
        if (str_contains($cleanPath, 'member-')) {
            return 'member_management';
        }
        if (str_contains($cleanPath, 'fund')) {
            return ($panelKey === 'admin') ? 'funds_management' : 'add_fund';
        }
        if (str_contains($cleanPath, 'staking')) {
            return ($panelKey === 'admin') ? 'staking_section' : 'investment';
        }
        if (str_contains($cleanPath, 'team') || str_contains($cleanPath, 'level')) {
            return 'team_management';
        }
        if (str_contains($cleanPath, 'support')) {
            return 'support';
        }
        if (str_contains($cleanPath, 'p2p')) {
            return 'p2p';
        }

        return null;
    }

    /**
     * Format standardized Section Discovery response and context.
     */
    protected function buildSectionDiscoveryPayload(string $secKey, array $secData, string $lang): array
    {
        $pageNames = array_keys($secData['pages']);
        $pageList = implode(', ', array_slice($pageNames, 0, -1));
        if (count($pageNames) > 1) {
            $pageList .= ($lang === 'english' ? ' and ' : ' aur ') . end($pageNames);
        } else {
            $pageList = $pageNames[0];
        }

        $speech = ($lang === 'english')
            ? "Under the {$secData['name']} section, there are " . count($pageNames) . " pages: {$pageList}. Which page would you like to open?"
            : "{$secData['name']} section mein yeh " . count($pageNames) . " pages hain: {$pageList}. Aap {$secData['name']} ke kis page par jaana chahte hain?";

        return [
            'success' => true,
            'intent' => 'clarify',
            'destination_key' => null,
            'target_url' => null,
            'speech' => $speech,
            'response_language' => $lang,
            'confidence' => 0.95,
            'needs_clarification' => true,
            'section_discovery' => [
                'section_key' => $secKey,
                'section_name' => $secData['name'],
                'pages' => $pageNames,
                'options' => $secData['pages'],
            ],
            'context' => [
                'pending_clarification' => 'section_page_choice',
                'pending_section' => $secKey,
                'pending_options' => $secData['pages'],
            ],
        ];
    }

    /**
     * Resolve intent using dedicated NavigationGeminiBridge (gemini-3.5-flash-lite).
     */
    public function resolveWithNavigationBridge(
        string $clean,
        string $role,
        string $currentPage,
        string $currentMode,
        string $lang,
        array $context
    ): ?array {
        try {
            if ($this->navGeminiBridge === null || !$this->navGeminiBridge->isConfigured()) {
                return null;
            }

            $res = $this->navGeminiBridge->resolveIntent($clean, $role, $currentPage, $lang, $context);
            if (!$res['success'] && in_array($res['status'], ['unconfigured', 'timeout', 'http_error', 'empty_speech', 'exception'], true)) {
                return null; // Fall through gracefully to smart fallback
            }

            $intent = $res['intent'] ?? 'unsupported_action';
            $destKey = $res['destination_key'] ?? null;
            $speech = $res['speech'] ?? null;
            $confidence = (float) ($res['confidence'] ?? 0.85);
            $responseLang = $res['response_language'] ?? $lang;

            // 1. Navigation Intent - Server Authoritative check
            if ($intent === 'navigate' && $destKey !== null) {
                if ($this->isDestinationAllowed($destKey, $role)) {
                    $url = $this->getDestinationUrl($destKey);
                    return [
                        'success' => true,
                        'intent' => 'navigate',
                        'destination_key' => $destKey,
                        'target_url' => $url,
                        'speech' => $speech ?: $this->buildNavigationSpeech($destKey, $responseLang),
                        'response_language' => $responseLang,
                        'confidence' => $confidence,
                        'needs_clarification' => false,
                        'context' => [],
                    ];
                }

                // If destination exists in app but not allowed for role -> unauthorized
                if (isset($this->destinations[$destKey])) {
                    return [
                        'success' => false,
                        'intent' => 'unauthorized',
                        'destination_key' => null,
                        'target_url' => null,
                        'speech' => $speech ?: ($responseLang === 'english'
                            ? "You do not have permission to access this section with your current account."
                            : "Aapke paas is section ko access karne ki permission nahi hai."),
                        'response_language' => $responseLang,
                        'confidence' => 0.98,
                        'needs_clarification' => false,
                        'context' => [],
                    ];
                }
            }

            // 2. Clarification Intent
            if ($intent === 'clarify') {
                $options = is_array($res['options'] ?? null) ? $res['options'] : [];
                return [
                    'success' => true,
                    'intent' => 'clarify',
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $speech ?: $this->buildSmartFallbackResponse($clean, $role, $currentPage, $responseLang)['speech'],
                    'response_language' => $responseLang,
                    'confidence' => $confidence,
                    'needs_clarification' => true,
                    'options' => $options,
                    'context' => !empty($options) ? [
                        'pending_clarification' => 'gemini_choice',
                        'pending_options' => $options,
                    ] : [],
                ];
            }

            // 3. Unauthorized Intent
            if ($intent === 'unauthorized') {
                return [
                    'success' => false,
                    'intent' => 'unauthorized',
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $speech ?: ($responseLang === 'english'
                        ? "This section is restricted to administrators only."
                        : "Yeh section aapke account role ke liye permitted nahi hai."),
                    'response_language' => $responseLang,
                    'confidence' => max($confidence, 0.95),
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }

            // 4. Window Controls & History
            if (in_array($intent, ['go_back', 'close_assistant', 'go_forward'], true)) {
                return [
                    'success' => true,
                    'intent' => $intent,
                    'destination_key' => null,
                    'target_url' => null,
                    'speech' => $speech ?: ($intent === 'close_assistant' ? 'Closing assistant window.' : 'Going back.'),
                    'response_language' => $responseLang,
                    'confidence' => $confidence,
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('[IraNavigationService] Bridge exception note: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Resolve intent using Gemini AI with strict role and schema enforcement.
     */
    public function resolveWithGemini(
        string $clean,
        string $role,
        string $currentPage,
        string $currentMode,
        string $lang,
        array $context
    ): ?array {
        try {
            $allowedKeys = array_keys($this->getAllowedDestinations($role));
            $allowedKeysList = implode(', ', $allowedKeys);

            $systemPrompt = <<<PROMPT
You are IRA, the central voice navigation assistant for Quantum ARB.
Understand natural Hindi, Hinglish, and English navigation queries.
Server-Verified Role: {$role}
Current Page: {$currentPage}
Permitted Destinations for this role: [{$allowedKeysList}]

Rules:
1. Intent:
   - "navigate": ONLY if matching one of [{$allowedKeysList}].
   - "clarify": if command is ambiguous or multiple pages match.
   - "unauthorized": if user requests another role's private section.
   - "unsupported_action": if request is unknown or not a navigation command.
2. If role is 'member' and user asks for Admin Dashboard or Member Details, return intent: "unauthorized".
3. Return strictly valid JSON:
{
  "intent": "navigate|go_back|close_assistant|clarify|unauthorized|unsupported_action",
  "destination_key": "string or null",
  "confidence": 0.0 to 1.0,
  "needs_clarification": false,
  "speech": "Natural conversational response",
  "response_language": "hinglish|english|hindi"
}
PROMPT;

            $contents = [
                ['role' => 'user', 'parts' => [
                    ['text' => $systemPrompt . "\n\nUser Utterance: \"" . $clean . "\""],
                ]],
            ];

            $response = $this->gemini->generate($contents);
            $rawText = trim($response['text'] ?? '');

            $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $rawText);
            $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);

            $parsed = json_decode($cleanJson, true);
            if (!is_array($parsed) || empty($parsed['intent'])) {
                return null;
            }

            $intent = $parsed['intent'];
            $destKey = $parsed['destination_key'] ?? null;
            $speech = $parsed['speech'] ?? '';
            $confidence = (float) ($parsed['confidence'] ?? 0.85);

            if ($intent === 'navigate') {
                if ($destKey && $this->isDestinationAllowed($destKey, $role)) {
                    $url = $this->getDestinationUrl($destKey);
                    return [
                        'success' => true,
                        'intent' => 'navigate',
                        'destination_key' => $destKey,
                        'target_url' => $url,
                        'speech' => $speech ?: $this->buildNavigationSpeech($destKey, $lang),
                        'response_language' => $lang,
                        'confidence' => $confidence,
                        'needs_clarification' => false,
                        'context' => [],
                    ];
                }

                // If destination exists but not for this role
                if ($destKey && isset($this->destinations[$destKey])) {
                    return [
                        'success' => false,
                        'intent' => 'unauthorized',
                        'destination_key' => null,
                        'target_url' => null,
                        'speech' => $lang === 'english'
                            ? "You do not have access to this section with your current account."
                            : "Aapke paas is section ko access karne ki permission nahi hai.",
                        'response_language' => $lang,
                        'confidence' => 0.95,
                        'needs_clarification' => false,
                        'context' => [],
                    ];
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('[IraNavigationService] Gemini note: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Resolve a specific destination key with role authorization gating.
     */
    public function resolveDestination(string $destinationKey, string $role, string $lang): array
    {
        if (!isset($this->destinations[$destinationKey])) {
            return [
                'success' => false,
                'intent' => 'unsupported_action',
                'destination_key' => null,
                'target_url' => null,
                'speech' => $lang === 'english'
                    ? "I couldn't find that page. Please tell me the section name, like Dashboard or Team."
                    : "Mujhe yeh page nahi mila. Kripya section ka naam batayein, jaise Dashboard ya Team.",
                'response_language' => $lang,
                'confidence' => 0.6,
                'needs_clarification' => false,
                'context' => [],
            ];
        }

        $config = $this->destinations[$destinationKey];

        // Authorization check
        if (!in_array($role, $config['roles'], true)) {
            if ($role === 'guest') {
                $targetLogin = str_starts_with($destinationKey, 'admin_') ? url('/admin') : url('/member');
                return [
                    'success' => false,
                    'intent' => 'unauthorized',
                    'destination_key' => str_starts_with($destinationKey, 'admin_') ? 'admin_login' : 'member_login',
                    'target_url' => $targetLogin,
                    'speech' => $lang === 'english'
                        ? "Please sign in to your account first. Opening the sign-in page."
                        : "Is page ke liye pehle login karein. Main login page khol rahi hoon.",
                    'response_language' => $lang,
                    'confidence' => 0.95,
                    'needs_clarification' => false,
                    'context' => [],
                ];
            }

            return [
                'success' => false,
                'intent' => 'unauthorized',
                'destination_key' => null,
                'target_url' => null,
                'speech' => $lang === 'english'
                    ? "Access Denied. You do not have permission to view this page."
                    : "Yeh page aapke role ke liye permitted nahi hai. Kripya authorized account se login karein.",
                'response_language' => $lang,
                'confidence' => 0.95,
                'needs_clarification' => false,
                'context' => [],
            ];
        }

        $targetUrl = url($config['path']);
        $speech = $this->buildNavigationSpeech($destinationKey, $lang);

        return [
            'success' => true,
            'intent' => 'navigate',
            'destination_key' => $destinationKey,
            'target_url' => $targetUrl,
            'speech' => $speech,
            'response_language' => $lang,
            'confidence' => 0.98,
            'needs_clarification' => false,
            'context' => [],
        ];
    }

    /**
     * Build natural voice confirmation speech for navigation destinations.
     */
    public function buildNavigationSpeech(string $destinationKey, string $lang): string
    {
        if ($lang === 'english') {
            return match ($destinationKey) {
                'member_dashboard' => "Opening your member dashboard.",
                'member_trading' => "Opening Arbitrage Trading.",
                'member_profile_summary' => "Opening Edit Profile.",
                'member_profile_security' => "Opening Change Password settings.",
                'member_profile_wallet' => "Opening Wallet Address settings.",
                'member_fund_import' => "Opening Add Fund page.",
                'member_fund_history' => "Opening Add Fund History.",
                'member_staking' => "Opening Create Investment portal.",
                'member_staking_details' => "Opening Investment Details.",
                'member_income_staking' => "Opening Staking Income.",
                'member_income_level' => "Opening Level Income.",
                'member_income_direct' => "Opening Direct Income.",
                'member_income_reward' => "Opening Reward Income.",
                'member_income_reward_details' => "Opening Reward Details.",
                'member_team_geneology' => "Opening your Team Geneology.",
                'member_team_direct' => "Opening Direct Members list.",
                'member_team_level_table' => "Opening Level Member Table.",
                'member_wallet_main' => "Opening Withdrawal Fund page.",
                'member_wallet_history' => "Opening Withdrawal History.",
                'member_wallet_to_p2p' => "Opening Wallet to P2P Transfer.",
                'member_wallet_transactions' => "Opening Transaction History.",
                'member_p2p_wallet' => "Opening P2P Wallet.",
                'member_p2p_history' => "Opening P2P History.",
                'member_support_create' => "Opening Create New Ticket form.",
                'member_support_tickets' => "Opening Support Tickets.",
                'member_login' => "Opening the Member sign-in page.",
                'admin_login' => "Opening the Admin sign-in page.",
                'admin_dashboard' => "Opening the Admin Dashboard.",
                'admin_member_details' => "Opening Members Details management.",
                'admin_account_control' => "Opening Account Control.",
                'admin_member_security' => "Opening Members Security.",
                'admin_free_pin_account' => "Opening Zero Pin Members.",
                'admin_wallet_address' => "Opening Member Wallet Address management.",
                'admin_funds_add' => "Opening Add Funds.",
                'admin_funds_deduct' => "Opening Deduct Funds.",
                'admin_funds_add_details' => "Opening Funds Details.",
                'admin_request_history' => "Opening Import Fund History.",
                'admin_staking_incomes' => "Opening Admin Staking Income.",
                'admin_staking_details' => "Opening Admin Staking Details.",
                'admin_income_level' => "Opening Admin Level Incomes.",
                'admin_income_reward' => "Opening Admin Reward Income.",
                'admin_income_direct' => "Opening Admin Direct Income.",
                'admin_new_withdrawals' => "Opening New Withdrawal Requests.",
                'admin_cancelled_requests' => "Opening Cancelled Requests.",
                'admin_payment_history' => "Opening Payment History.",
                'admin_support_new' => "Opening New Support Tickets.",
                'admin_support_open' => "Opening Open Support Tickets.",
                'admin_support_close' => "Opening Closed Support Tickets.",
                'admin_popup_image' => "Opening Popup and Dashboard Images.",
                'admin_dash_messages' => "Opening Dashboard Messages.",
                'admin_profile' => "Opening Administrator Profile.",
                'home' => "Navigating to the homepage.",
                'gallery' => "Opening the public gallery.",
                default => "Navigating now.",
            };
        }

        // Natural everyday Hinglish
        return match ($destinationKey) {
            'member_dashboard' => "Bilkul! Dashboard khol rahi hoon.",
            'member_trading' => "Ji, arbitrage trading desk khol rahi hoon.",
            'member_profile_summary' => "Aapka Edit Profile page khol rahi hoon.",
            'member_profile_security' => "Change password aur security settings par chalte hain.",
            'member_profile_wallet' => "Wallet address settings khol rahi hoon.",
            'member_fund_import' => "Add Fund page khol rahi hoon.",
            'member_fund_history' => "Add Fund History page par chalte hain.",
            'member_staking' => "Ji, Create Investment portal khol rahi hoon.",
            'member_staking_details' => "Investment details khol rahi hoon.",
            'member_income_staking' => "Aapka Staking income page khol rahi hoon.",
            'member_income_level' => "Level income report khol rahi hoon.",
            'member_income_direct' => "Theek hai, Direct income dikha rahi hoon.",
            'member_income_reward' => "Reward income report khol rahi hoon.",
            'member_income_reward_details' => "Reward details page par chalte hain.",
            'member_team_geneology' => "Ji, aapka Team geneology tree khol rahi hoon.",
            'member_team_direct' => "Direct members ki list khol rahi hoon.",
            'member_team_level_table' => "Level member table khol rahi hoon.",
            'member_wallet_main' => "Withdrawal Fund page khol rahi hoon.",
            'member_wallet_history' => "Withdrawal history wala page khol rahi hoon.",
            'member_wallet_to_p2p' => "Wallet to P2P transfer page khol rahi hoon.",
            'member_wallet_transactions' => "Aapka transaction history ledger khol rahi hoon.",
            'member_p2p_wallet' => "P2P wallet khol rahi hoon.",
            'member_p2p_history' => "P2P history page par chalte hain.",
            'member_support_create' => "Naya support ticket create karne ka page khol rahi hoon.",
            'member_support_tickets' => "Aapke support tickets khol rahi hoon.",
            'member_login' => "Member login page khol rahi hoon.",
            'admin_login' => "Admin login page khol rahi hoon.",
            'admin_dashboard' => "Ji, Admin dashboard khol rahi hoon.",
            'admin_member_details' => "Members details management khol rahi hoon.",
            'admin_account_control' => "Account control section par chalte hain.",
            'admin_member_security' => "Members security settings khol rahi hoon.",
            'admin_free_pin_account' => "Zero pin members list khol rahi hoon.",
            'admin_wallet_address' => "Member wallet address management khol rahi hoon.",
            'admin_funds_add' => "Add funds page khol rahi hoon.",
            'admin_funds_deduct' => "Deduct funds page khol rahi hoon.",
            'admin_funds_add_details' => "Funds details page par chalte hain.",
            'admin_request_history' => "Import fund history khol rahi hoon.",
            'admin_staking_incomes' => "Staking income report khol rahi hoon.",
            'admin_staking_details' => "Staking details page par chalte hain.",
            'admin_income_level' => "Level incomes report khol rahi hoon.",
            'admin_income_reward' => "Reward income report khol rahi hoon.",
            'admin_income_direct' => "Direct income report khol rahi hoon.",
            'admin_new_withdrawals' => "New withdrawal requests khol rahi hoon.",
            'admin_cancelled_requests' => "Cancelled requests khol rahi hoon.",
            'admin_payment_history' => "Payment history khol rahi hoon.",
            'admin_support_new' => "New support tickets khol rahi hoon.",
            'admin_support_open' => "Open support tickets khol rahi hoon.",
            'admin_support_close' => "Closed support tickets khol rahi hoon.",
            'admin_popup_image' => "Popup aur dashboard images khol rahi hoon.",
            'admin_dash_messages' => "Dashboard messages page par chalte hain.",
            'admin_profile' => "Administrator profile khol rahi hoon.",
            'home' => "Main website home page par chalte hain.",
            'gallery' => "Public gallery khol rahi hoon.",
            default => "Ji, page khol rahi hoon.",
        };
    }

    /**
     * Provide contextual explanation of current page.
     */
    public function buildPageExplanation(string $currentPage, string $role, string $lang): array
    {
        $speech = "";
        if (str_contains($currentPage, 'trading')) {
            $speech = $lang === 'english'
                ? "This is the Arbitrage Trading portal, where real-time market opportunities are monitored."
                : "Yeh Arbitrage Trading portal hai, yahan real-time market opportunities track hoti hain.";
        } elseif (str_contains($currentPage, 'staking')) {
            $speech = $lang === 'english'
                ? "This is your Investment and Staking section where you can create new investments and track active returns."
                : "Yeh aapka Investment aur Staking portal hai. Yahan aap nayi investment create kar sakte hain aur details dekh sakte hain.";
        } elseif (str_contains($currentPage, 'wallet')) {
            $speech = $lang === 'english'
                ? "This is your Withdrawal and Wallet portal for requesting fund withdrawals and reviewing transactions."
                : "Yeh aapka Wallet aur Withdrawal portal hai. Yahan aap withdrawal request kar sakte hain aur transaction history dekh sakte hain.";
        } elseif (str_contains($currentPage, 'team') || str_contains($currentPage, 'levels')) {
            $speech = $lang === 'english'
                ? "This is your Team Management section showing your geneology tree and direct team members."
                : "Yeh aapka Team Management section hai jahan geneology tree aur direct team members dekh sakte hain.";
        } elseif (str_contains($currentPage, 'admin')) {
            $speech = $lang === 'english'
                ? "This is the Administrator portal for system management, user accounts, and withdrawals."
                : "Yeh Quantum ARB Admin portal hai jahan se authorized administrators system manage karte hain.";
        } else {
            $speech = $lang === 'english'
                ? "This is your Dashboard providing an overview of your account, active balance, and earnings."
                : "Yeh aapka Dashboard hai, yahan aapko aapki total earnings aur account ka quick overview milta hai.";
        }

        return [
            'success' => true,
            'intent' => 'general_question',
            'destination_key' => null,
            'target_url' => null,
            'speech' => $speech,
            'response_language' => $lang,
            'confidence' => 0.95,
            'needs_clarification' => false,
            'context' => [],
        ];
    }

    /**
     * Build intelligent, varied fallback when an utterance is truly unclear.
     */
    public function buildSmartFallbackResponse(string $clean, string $role, string $currentPage, string $lang): array
    {
        $panel = ($role === 'admin') ? 'Admin' : 'Member';
        
        $speech = ($lang === 'english')
            ? "I didn't quite catch that. Would you like to go to the {$panel} Dashboard, view your Profile, or open a specific sidebar section?"
            : "Maaf kijiye, main samajh nahi paayi. Aap kis page par jaana chahte hain? Jaise Dashboard, Profile, ya koi section ka naam batayein.";

        return [
            'success' => true,
            'intent' => 'clarify',
            'destination_key' => null,
            'target_url' => null,
            'speech' => $speech,
            'response_language' => $lang,
            'confidence' => 0.65,
            'needs_clarification' => true,
            'context' => [],
        ];
    }

    /**
     * Normalize conversational utterance by trimming filler phrasing.
     */
    protected function normalizeUtterance(string $text): string
    {
        $lower = mb_strtolower(trim($text), 'UTF-8');
        
        // Remove common conversational framing safely without stripping distinguishing tokens
        $patterns = [
            '/^(?:mujhe|humko|humein|mera|meri|hamara|please|kripya|yaar|bhai|मुझे|कृपया)\s+/iu',
            '/\b(?:ke\s+page\s+par|page\s+par|page\s+pe|par|pe|ke\s+paas)\b/iu',
            '/(?:के\s*पेज\s*पर|पेज\s*पर|पेज\s*पे|पर|पे)/u',
            '/\b(?:le\s+chalo|le\s+jao|jaana\s+hai|khol\s+do|kholiye|kholo|dikha\s+do|dikhao|open\s+karo|show\s+karo)\b/iu',
            '/(?:ले\s*चलो|ले\s*जाओ|जाना\s*है|खोल\s*दो|खोलिए|खोलो|दिखा\s*दो|दिखाओ|ओपन\s*करो|शो\s*करो)/u',
            '/\b(?:can\s+you\s+open|take\s+me\s+to|i\s+want\s+to\s+see|show\s+me|open\s+the|go\s+to\s+the|navigate\s+to)\b/iu',
            '/\b(?:wala\s+page|wali\s+screen)\b/iu',
            '/(?:वाला\s*पेज|वाली\s*स्क्रीन)/u',
        ];

        $cleaned = preg_replace($patterns, ' ', $lower);
        return trim(preg_replace('/\s+/u', ' ', $cleaned));
    }

    /**
     * Detect whether the input is Hindi (Devanagari), Hinglish (Roman), or English.
     */
    public function detectLanguage(string $text): string
    {
        if (preg_match('/[\x{0900}-\x{097F}]/u', $text)) {
            return 'hindi';
        }

        if (preg_match('/\b(kholo|dikhao|chalo|jao|pichhe|peechhe|mujhe|meri|mera|batao|karna|hai|nahi|haan)\b/iu', $text)) {
            return 'hinglish';
        }

        return 'english';
    }

    /**
     * Check if a destination is allowed for a given role.
     */
    public function isDestinationAllowed(string $destinationKey, string $role): bool
    {
        return isset($this->destinations[$destinationKey])
            && in_array($role, $this->destinations[$destinationKey]['roles'], true);
    }

    /**
     * Get absolute URL for a destination key.
     */
    public function getDestinationUrl(string $destinationKey): ?string
    {
        if (!isset($this->destinations[$destinationKey])) {
            return null;
        }

        return url($this->destinations[$destinationKey]['path']);
    }

    /**
     * Return all allowed destination configurations for a role.
     */
    public function getAllowedDestinations(string $role): array
    {
        $allowed = [];
        foreach ($this->destinations as $key => $config) {
            if (in_array($role, $config['roles'], true)) {
                $allowed[$key] = [
                    'label' => $config['label'],
                    'url' => url($config['path']),
                    'section' => $config['section'] ?? null,
                ];
            }
        }
        return $allowed;
    }

    /**
     * Return entire destination registry array for inspection/testing.
     */
    public function getDestinations(): array
    {
        return $this->destinations;
    }

    /**
     * Return sidebar sections array for inspection/testing.
     */
    public function getSidebarSections(): array
    {
        return $this->sidebarSections;
    }
}
