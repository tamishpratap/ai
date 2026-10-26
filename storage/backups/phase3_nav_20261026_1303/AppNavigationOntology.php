<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Route;

/**
 * AppNavigationOntology
 *
 * Canonical application-aware navigation knowledge layer for Quantum ARB / AI Demo.
 * Represents verified Member, Admin, and Public portals, exact section/child hierarchies,
 * bilingual aliases (Devanagari Hindi, Roman Hinglish, English), role restrictions,
 * and route validity verification.
 */
class AppNavigationOntology
{
    /**
     * Canonical registered destinations with verified GET routes.
     */
    protected static array $destinations = [
        // ==========================================
        // Public Destinations
        // ==========================================
        'home' => [
            'key' => 'home',
            'path' => '/',
            'label' => 'Home Page',
            'panel' => 'public',
            'section' => 'public',
            'type' => 'page',
            'roles' => ['guest', 'member', 'admin'],
            'aliases' => [
                'home', 'homepage', 'website', 'main website', 'public home', 'front end', 'frontend',
                'होम पेज', 'होमपेज', 'वेबसाइट', 'मेन वेबसाइट', 'फ्रंटएंड'
            ],
        ],
        'gallery' => [
            'key' => 'gallery',
            'path' => '/gallery',
            'label' => 'Gallery',
            'panel' => 'public',
            'section' => 'public',
            'type' => 'page',
            'roles' => ['guest', 'member', 'admin'],
            'aliases' => [
                'gallery', 'photo gallery', 'public gallery', 'photos',
                'गैलरी', 'फोटो गैलरी', 'पब्लिक गैलरी'
            ],
        ],
        'member_login' => [
            'key' => 'member_login',
            'path' => '/member',
            'label' => 'Member Sign In',
            'panel' => 'public',
            'section' => 'public',
            'type' => 'page',
            'roles' => ['guest', 'member', 'admin'],
            'aliases' => [
                'member login', 'sign in', 'user login', 'login page', 'login', 'sign in page', 'sign up', 'register',
                'मेंबर लॉगिन', 'लॉगिन', 'साइन इन', 'लॉगिन पेज', 'साइन अप', 'रजिस्टर'
            ],
        ],
        'admin_login' => [
            'key' => 'admin_login',
            'path' => '/admin',
            'label' => 'Administrator Sign In',
            'panel' => 'public',
            'section' => 'public',
            'type' => 'page',
            'roles' => ['guest', 'member', 'admin'],
            'aliases' => [
                'admin login', 'administrator sign in', 'admin portal', 'admin sign in',
                'एडमिन लॉगिन', 'एडमिन पोर्टल', 'एडमिन साइन इन'
            ],
        ],
        'forget_password' => [
            'key' => 'forget_password',
            'path' => '/forget-password',
            'label' => 'Admin Password Reset',
            'panel' => 'public',
            'section' => 'public',
            'type' => 'page',
            'roles' => ['guest', 'admin'],
            'aliases' => [
                'admin forget password', 'admin reset password', 'forget password',
                'एडमिन पासवर्ड रीसेट', 'फॉरगेट पासवर्ड'
            ],
        ],
        'member_forget_password' => [
            'key' => 'member_forget_password',
            'path' => '/member-forget-password',
            'label' => 'Member Password Reset',
            'panel' => 'public',
            'section' => 'public',
            'type' => 'page',
            'roles' => ['guest', 'member'],
            'aliases' => [
                'member forget password', 'member reset password', 'password bhool gaye',
                'मेंबर पासवर्ड रीसेट', 'मेंबर फॉरगेट पासवर्ड'
            ],
        ],

        // ==========================================
        // Member Protected Destinations (from Member Sidebar)
        // ==========================================
        'member_dashboard' => [
            'key' => 'member_dashboard',
            'path' => '/member/dashboard',
            'label' => 'Dashboard',
            'panel' => 'member',
            'section' => 'dashboard',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'dashboard', 'main dashboard', 'member dashboard', 'home dashboard',
                'dashbord', 'member dashbord', 'main dashbord', 'home dashbord',
                'डैशबोर्ड', 'डैश बोर्ड', 'मेन डैशबोर्ड', 'मेंबर डैशबोर्ड'
            ],
        ],
        'member_profile_summary' => [
            'key' => 'member_profile_summary',
            'path' => '/member/profile/user-summary',
            'label' => 'Edit Profile',
            'panel' => 'member',
            'section' => 'profile',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'edit profile', 'user summary', 'my profile', 'meri profile', 'profile details', 'profile',
                'profiel', 'my profiel', 'meri profiel', 'edit profiel',
                'एडिट प्रोफाइल', 'यूजर समरी', 'मेरी प्रोफाइल', 'प्रोफाइल डिटेल्स', 'प्रोफाइल'
            ],
        ],
        'member_profile_security' => [
            'key' => 'member_profile_security',
            'path' => '/member/profile/security',
            'label' => 'Change Password',
            'panel' => 'member',
            'section' => 'profile',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'change password', 'password change', 'security settings', 'password badlo', 'security',
                'पासवर्ड बदलो', 'पासवर्ड चेंज', 'सिक्योरिटी सेटिंग्स', 'सिक्योरिटी'
            ],
        ],
        'member_profile_wallet' => [
            'key' => 'member_profile_wallet',
            'path' => '/member/profile/wallet-address',
            'label' => 'Settings (Wallet Address)',
            'panel' => 'member',
            'section' => 'profile',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'settings wallet address', 'wallet address settings', 'my wallet address', 'wallet address',
                'वॉलेट एड्रेस सेटिंग्स', 'वॉलेट एड्रेस', 'मेरा वॉलेट एड्रेस'
            ],
        ],
        'member_fund_import' => [
            'key' => 'member_fund_import',
            'path' => '/member/fund/import-fund',
            'label' => 'Add Fund',
            'panel' => 'member',
            'section' => 'add_fund',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'add fund', 'import fund', 'deposit fund', 'deposit money', 'fund add karo', 'deposit',
                'ऐड फंड', 'फंड ऐड', 'डिपॉजिट फंड', 'इम्पोर्ट फंड', 'पैसे ऐड करो', 'फंड जमा करो'
            ],
        ],
        'member_fund_history' => [
            'key' => 'member_fund_history',
            'path' => '/member/fund/import-fund-history',
            'label' => 'Add Fund History',
            'panel' => 'member',
            'section' => 'add_fund',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'add fund history', 'import fund history', 'deposit history', 'fund history', 'fund deposit history',
                'ऐड फंड हिस्ट्री', 'इम्पोर्ट फंड हिस्ट्री', 'फंड हिस्ट्री', 'डिपॉजिट हिस्ट्री'
            ],
        ],
        'member_staking' => [
            'key' => 'member_staking',
            'path' => '/member/staking',
            'label' => 'Create Investment',
            'panel' => 'member',
            'section' => 'investment',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'create investment', 'new investment', 'investment portal', 'staking portal', 'invest karo', 'staking', 'invest',
                'क्रिएट इन्वेस्टमेंट', 'नया इन्वेस्टमेंट', 'स्टेकिंग', 'इन्वेस्ट करो', 'स्टेकिंग पोर्टल'
            ],
        ],
        'member_staking_details' => [
            'key' => 'member_staking_details',
            'path' => '/member/staking/staking-details',
            'label' => 'Investment Details',
            'panel' => 'member',
            'section' => 'investment',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'investment details', 'staking details', 'my investments', 'investment record', 'staking history',
                'इन्वेस्टमेंट डिटेल्स', 'स्टेकिंग डिटेल्स', 'निवेश विवरण', 'मेरी इन्वेस्टमेंट'
            ],
        ],
        'member_income_staking' => [
            'key' => 'member_income_staking',
            'path' => '/member/income/staking-incomes',
            'label' => 'Staking Income',
            'panel' => 'member',
            'section' => 'earnings',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'staking income', 'stake income', 'roi income', 'staking earnings',
                'स्टेकिंग इनकम', 'स्टेक इनकम', 'आरओआई इनकम', 'स्टेकिंग कमाई'
            ],
        ],
        'member_income_level' => [
            'key' => 'member_income_level',
            'path' => '/member/income/level-incomes',
            'label' => 'Level Income',
            'panel' => 'member',
            'section' => 'earnings',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'level income', 'levels income', 'team income', 'level earnings',
                'लेवल इनकम', 'लेवल्स इनकम', 'टीम इनकम', 'लेवल कमाई'
            ],
        ],
        'member_income_direct' => [
            'key' => 'member_income_direct',
            'path' => '/member/income/direct-incomes',
            'label' => 'Direct Income',
            'panel' => 'member',
            'section' => 'earnings',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'direct income', 'referral income', 'direct bonus', 'sponsor income',
                'डायरेक्ट इनकम', 'रेफरल इनकम', 'डायरेक्ट बोनस', 'डायरेक्ट कमाई'
            ],
        ],
        'member_income_reward' => [
            'key' => 'member_income_reward',
            'path' => '/member/income/reward-incomes',
            'label' => 'Reward Income',
            'panel' => 'member',
            'section' => 'reward_section',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'reward income', 'rewards income', 'inam income', 'reward kamai',
                'रिवॉर्ड इनकम', 'रिवार्ड इनकम', 'इनाम इनकम', 'रिवार्ड्स इनकम'
            ],
        ],
        'member_income_reward_details' => [
            'key' => 'member_income_reward_details',
            'path' => '/member/income/reward-details',
            'label' => 'Reward Details',
            'panel' => 'member',
            'section' => 'reward_section',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'reward details', 'rewards details', 'rewards status', 'career rewards',
                'रिवॉर्ड डिटेल्स', 'रिवार्ड डिटेल्स', 'इनाम विवरण', 'रिवार्ड स्टेटस'
            ],
        ],
        'member_team_geneology' => [
            'key' => 'member_team_geneology',
            'path' => '/member/team/geneology',
            'label' => 'Geneology',
            'panel' => 'member',
            'section' => 'team_management',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'geneology', 'genealogy', 'team tree', 'network tree', 'downline tree', 'tree',
                'जीनोलॉजी', 'ट्री', 'टीम ट्री', 'नेटवर्क ट्री', 'डाउनलाइन ट्री'
            ],
        ],
        'member_team_direct' => [
            'key' => 'member_team_direct',
            'path' => '/member/levels/direct-members',
            'label' => 'Direct Members',
            'panel' => 'member',
            'section' => 'team_management',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'direct members', 'direct team', 'my directs', 'direct sponsor list',
                'डायरेक्ट मेंबर्स', 'डायरेक्ट टीम', 'मेरे डायरेक्ट्स', 'डायरेक्ट लिस्ट'
            ],
        ],
        'member_team_level_table' => [
            'key' => 'member_team_level_table',
            'path' => '/member/levels/level-members-table',
            'label' => 'Level Member Table',
            'panel' => 'member',
            'section' => 'team_management',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'level member table', 'level members', 'level table', 'all level team',
                'लेवल मेंबर टेबल', 'लेवल मेंबर्स', 'लेवल टेबल', 'टीम टेबल'
            ],
        ],
        'member_wallet_main' => [
            'key' => 'member_wallet_main',
            'path' => '/member/wallet',
            'label' => 'Withdrawal Fund',
            'panel' => 'member',
            'section' => 'withdrawal_section',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'withdrawal fund', 'withdraw fund', 'main wallet', 'nikasi fund', 'paisa nikalo', 'withdraw money',
                'withdrawal page', 'withdraw page', 'withdrawal', 'withdraw', 'withdrawl', 'withdrawl page', 'withdrawl fund',
                'विड्रॉल फंड', 'विथड्रॉल फंड', 'पैसे निकालो', 'मेन वॉलेट', 'निकासी फंड', 'विथड्रॉल पेज', 'विड्रॉल पेज', 'विथड्रॉल', 'विड्रॉल'
            ],
        ],
        'member_wallet_history' => [
            'key' => 'member_wallet_history',
            'path' => '/member/wallet/wallet-history',
            'label' => 'Withdrawal History',
            'panel' => 'member',
            'section' => 'withdrawal_section',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'withdrawal history', 'nikasi history', 'past withdrawals', 'withdrawal records',
                'विड्रॉल हिस्ट्री', 'विथड्रॉल हिस्ट्री', 'निकासी हिस्ट्री', 'विड्रॉल रिकॉर्ड्स'
            ],
        ],
        'member_wallet_to_p2p' => [
            'key' => 'member_wallet_to_p2p',
            'path' => '/member/wallet/wallet-to-p2p',
            'label' => 'Wallet to P2P Transfer',
            'panel' => 'member',
            'section' => 'withdrawal_section',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'wallet to p2p transfer', 'wallet to p2p', 'transfer to p2p', 'wallet p2p transfer',
                'वॉलेट टू पी2पी', 'वॉलेट टू पी टू पी', 'पी2पी ट्रांसफर'
            ],
        ],
        'member_wallet_transactions' => [
            'key' => 'member_wallet_transactions',
            'path' => '/member/wallet/all-transaction',
            'label' => 'Transaction History',
            'panel' => 'member',
            'section' => 'withdrawal_section',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'transaction history', 'all transactions', 'wallet ledger', 'transactions report',
                'ट्रांजेक्शन हिस्ट्री', 'सारे ट्रांजेक्शन', 'वॉलेट लेजर', 'लेनदेन इतिहास'
            ],
        ],
        'member_p2p_wallet' => [
            'key' => 'member_p2p_wallet',
            'path' => '/member/p2p-wallet',
            'label' => 'P2P Wallet',
            'panel' => 'member',
            'section' => 'p2p',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'p2p wallet', 'p to p wallet', 'peer to peer wallet', 'petrol wallet',
                'पी2पी वॉलेट', 'पी टू पी वॉलेट', 'पीयर टू पीयर वॉलेट'
            ],
        ],
        'member_p2p_history' => [
            'key' => 'member_p2p_history',
            'path' => '/member/p2p-wallet-history',
            'label' => 'P2P History',
            'panel' => 'member',
            'section' => 'p2p',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'p2p history', 'p to p history', 'p2p transfers', 'peer to peer history', 'petrol history',
                'पी2पी हिस्ट्री', 'पी टू पी हिस्ट्री', 'पीयर टू पीयर हिस्ट्री'
            ],
        ],
        'member_support_create' => [
            'key' => 'member_support_create',
            'path' => '/member/support/create-ticket',
            'label' => 'Create New Ticket',
            'panel' => 'member',
            'section' => 'support',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'create new ticket', 'raise ticket', 'new support ticket', 'naya ticket', 'open ticket',
                'क्रिएट न्यू टिकट', 'नया टिकट', 'सपोर्ट टिकट बनाओ', 'टिकट रेज करो'
            ],
        ],
        'member_support_tickets' => [
            'key' => 'member_support_tickets',
            'path' => '/member/support/support-tickets',
            'label' => 'Support Tickets',
            'panel' => 'member',
            'section' => 'support',
            'type' => 'page',
            'roles' => ['member'],
            'aliases' => [
                'support tickets', 'my tickets', 'ticket history', 'helpdesk tickets',
                'सपोर्ट टिकट्स', 'मेरे टिकट्स', 'टिकट हिस्ट्री', 'सपोर्ट हिस्ट्री'
            ],
        ],
        'member_logout' => [
            'key' => 'member_logout',
            'path' => '/member/logout',
            'label' => 'Logout',
            'panel' => 'member',
            'section' => 'logout',
            'type' => 'action',
            'roles' => ['member'],
            'aliases' => [
                'logout', 'sign out', 'log out', 'bahar niklo',
                'लॉगआउट', 'साइन आउट', 'बाहर निकलो'
            ],
        ],

        // ==========================================
        // Admin Protected Destinations (from Admin Sidebar)
        // ==========================================
        'admin_dashboard' => [
            'key' => 'admin_dashboard',
            'path' => '/admin/dashboard',
            'label' => 'Dashboard',
            'panel' => 'admin',
            'section' => 'dashboard',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'admin dashboard', 'administrator dashboard', 'main admin dashboard',
                'एडमिन डैशबोर्ड', 'एडमिनिस्ट्रेटर डैशबोर्ड', 'मेन एडमिन डैशबोर्ड'
            ],
        ],
        'admin_popup_image' => [
            'key' => 'admin_popup_image',
            'path' => '/admin/popup-image',
            'label' => 'Popup / Dashboard Image',
            'panel' => 'admin',
            'section' => 'dashboard',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'popup image', 'dashboard image', 'popup dashboard image', 'achievers images',
                'पॉपअप इमेज', 'डैशबोर्ड इमेज', 'पॉपअप / डैशबोर्ड इमेज'
            ],
        ],
        'admin_dash_messages' => [
            'key' => 'admin_dash_messages',
            'path' => '/admin/dash-messages',
            'label' => 'Dashboard Messages',
            'panel' => 'admin',
            'section' => 'dashboard',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'dashboard messages', 'dash messages', 'admin announcements', 'broadcasts',
                'डैशबोर्ड मैसेज', 'डैश मैसेज', 'एडमिन अनाउंसमेंट'
            ],
        ],
        'admin_member_details' => [
            'key' => 'admin_member_details',
            'path' => '/admin/member-details',
            'label' => 'Members Details',
            'panel' => 'admin',
            'section' => 'member_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'members details', 'member details', 'user list', 'all members', 'member list',
                'मेंबर्स डिटेल्स', 'मेंबर डिटेल्स', 'यूजर लिस्ट', 'सारे मेंबर्स'
            ],
        ],
        'admin_account_control' => [
            'key' => 'admin_account_control',
            'path' => '/admin/account-control',
            'label' => 'Account Control',
            'panel' => 'admin',
            'section' => 'member_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'account control', 'member account control', 'block user', 'user status',
                'अकाउंट कंट्रोल', 'मेंबर अकाउंट कंट्रोल', 'यूजर ब्लॉक'
            ],
        ],
        'admin_member_security' => [
            'key' => 'admin_member_security',
            'path' => '/admin/member-security',
            'label' => 'Members Security',
            'panel' => 'admin',
            'section' => 'member_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'members security', 'member security', 'reset member password', 'user security',
                'मेंबर्स सिक्योरिटी', 'मेंबर सिक्योरिटी', 'यूजर सिक्योरिटी'
            ],
        ],
        'admin_free_pin_account' => [
            'key' => 'admin_free_pin_account',
            'path' => '/admin/free-pin-account',
            'label' => 'Zero Pin Members',
            'panel' => 'admin',
            'section' => 'member_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'zero pin members', 'free pin account', 'free pin members', 'pin accounts',
                'जीरो पिन मेंबर्स', 'फ्री पिन अकाउंट', 'पिन अकाउंट्स'
            ],
        ],
        'admin_wallet_address' => [
            'key' => 'admin_wallet_address',
            'path' => '/admin/wallet-address',
            'label' => 'Member Wallet Address',
            'panel' => 'admin',
            'section' => 'member_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'member wallet address', 'users wallet address', 'admin wallet address',
                'मेंबर वॉलेट एड्रेस', 'यूजर्स वॉलेट एड्रेस'
            ],
        ],
        'admin_funds_add' => [
            'key' => 'admin_funds_add',
            'path' => '/admin/funds/add-funds',
            'label' => 'Add Funds',
            'panel' => 'admin',
            'section' => 'funds_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'add funds', 'credit funds', 'admin add fund', 'credit balance',
                'ऐड फंड्स', 'क्रेडिट फंड्स', 'एडमिन ऐड फंड', 'फंड क्रेडिट करो'
            ],
        ],
        'admin_funds_deduct' => [
            'key' => 'admin_funds_deduct',
            'path' => '/admin/funds/deduct-funds',
            'label' => 'Deduct Funds',
            'panel' => 'admin',
            'section' => 'funds_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'deduct funds', 'debit funds', 'admin deduct fund', 'fund kaato',
                'डिडक्ट फंड्स', 'डेबिट फंड्स', 'एडमिन डिडक्ट फंड', 'फंड काटो'
            ],
        ],
        'admin_funds_add_details' => [
            'key' => 'admin_funds_add_details',
            'path' => '/admin/funds/add-funds-details',
            'label' => 'Funds Details',
            'panel' => 'admin',
            'section' => 'funds_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'funds details', 'funds history', 'credit debit history', 'fund transfer details',
                'फंड्स डिटेल्स', 'फंड्स हिस्ट्री', 'क्रेडिट डेबिट हिस्ट्री'
            ],
        ],
        'admin_request_history' => [
            'key' => 'admin_request_history',
            'path' => '/admin/request-history',
            'label' => 'Import Fund History',
            'panel' => 'admin',
            'section' => 'funds_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'import fund history', 'request history', 'deposit requests history',
                'इम्पोर्ट फंड हिस्ट्री', 'रिक्वेस्ट हिस्ट्री', 'डिपॉजिट रिक्वेस्ट्स'
            ],
        ],
        'admin_staking_incomes' => [
            'key' => 'admin_staking_incomes',
            'path' => '/admin/incomes/staking-incomes',
            'label' => 'Staking Income',
            'panel' => 'admin',
            'section' => 'staking_section',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'admin staking income', 'staking incomes report', 'staking roi report',
                'एडमिन स्टेकिंग इनकम', 'स्टेकिंग इनकम रिपोर्ट'
            ],
        ],
        'admin_staking_details' => [
            'key' => 'admin_staking_details',
            'path' => '/admin/incomes/staking-details',
            'label' => 'Staking Details',
            'panel' => 'admin',
            'section' => 'staking_section',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'admin staking details', 'staking records', 'all member stakings',
                'एडमिन स्टेकिंग डिटेल्स', 'स्टेकिंग रिकॉर्ड्स'
            ],
        ],
        'admin_income_level' => [
            'key' => 'admin_income_level',
            'path' => '/admin/incomes/level-incomes',
            'label' => 'Level Incomes',
            'panel' => 'admin',
            'section' => 'income_section',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'admin level incomes', 'level income report', 'all level earnings',
                'एडमिन लेवल इनकम्स', 'लेवल इनकम रिपोर्ट'
            ],
        ],
        'admin_income_reward' => [
            'key' => 'admin_income_reward',
            'path' => '/admin/incomes/reward-incomes',
            'label' => 'Reward Income',
            'panel' => 'admin',
            'section' => 'income_section',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'admin reward income', 'reward incomes report', 'member rewards payout',
                'एडमिन रिवॉर्ड इनकम', 'रिवार्ड इनकम रिपोर्ट'
            ],
        ],
        'admin_income_direct' => [
            'key' => 'admin_income_direct',
            'path' => '/admin/incomes/direct-incomes',
            'label' => 'Direct Income',
            'panel' => 'admin',
            'section' => 'income_section',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'admin direct income', 'direct income report', 'referral payout report',
                'एडमिन डायरेक्ट इनकम', 'डायरेक्ट इनकम रिपोर्ट'
            ],
        ],
        'admin_new_withdrawals' => [
            'key' => 'admin_new_withdrawals',
            'path' => '/admin/new-withdrawal-request',
            'label' => 'New Withdrawal Requests',
            'panel' => 'admin',
            'section' => 'payment_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'new withdrawal requests', 'pending withdrawals', 'withdrawal approval',
                'नई विड्रॉल रिक्वेस्ट', 'पेंडिंग विड्रॉल', 'विड्रॉल अप्रूवल'
            ],
        ],
        'admin_cancelled_requests' => [
            'key' => 'admin_cancelled_requests',
            'path' => '/admin/cancelled-request',
            'label' => 'Cancelled Requests',
            'panel' => 'admin',
            'section' => 'payment_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'cancelled requests', 'rejected withdrawals', 'cancelled withdrawal list',
                'कैंसिल्ड रिक्वेस्ट्स', 'रिजेक्टेड विड्रॉल', 'कैंसिल्ड विड्रॉल'
            ],
        ],
        'admin_payment_history' => [
            'key' => 'admin_payment_history',
            'path' => '/admin/payment-history',
            'label' => 'Payment History',
            'panel' => 'admin',
            'section' => 'payment_management',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'payment history', 'payout history', 'withdrawal completed history',
                'पेमेंट हिस्ट्री', 'पेआउट हिस्ट्री', 'विड्रॉल हिस्ट्री'
            ],
        ],
        'admin_support_new' => [
            'key' => 'admin_support_new',
            'path' => '/admin/support/new-support-ticket',
            'label' => 'New Support Tickets',
            'panel' => 'admin',
            'section' => 'support',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'new support tickets', 'unassigned tickets', 'incoming support requests',
                'नई सपोर्ट टिकट्स', 'न्यू सपोर्ट टिकट्स'
            ],
        ],
        'admin_support_open' => [
            'key' => 'admin_support_open',
            'path' => '/admin/support/open-support-ticket',
            'label' => 'Open Support Tickets',
            'panel' => 'admin',
            'section' => 'support',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'open support tickets', 'pending tickets', 'in-progress tickets',
                'ओपन सपोर्ट टिकट्स', 'पेंडिंग टिकट्स'
            ],
        ],
        'admin_support_close' => [
            'key' => 'admin_support_close',
            'path' => '/admin/support/close-support-ticket',
            'label' => 'Closed Support Tickets',
            'panel' => 'admin',
            'section' => 'support',
            'type' => 'page',
            'roles' => ['admin'],
            'aliases' => [
                'closed support tickets', 'resolved tickets', 'completed tickets',
                'क्लोज्ड सपोर्ट टिकट्स', 'सुलझे हुए टिकट्स'
            ],
        ],
        'admin_logout' => [
            'key' => 'admin_logout',
            'path' => '/admin/logout',
            'label' => 'Logout',
            'panel' => 'admin',
            'section' => 'logout',
            'type' => 'action',
            'roles' => ['admin'],
            'aliases' => [
                'admin logout', 'sign out', 'log out', 'admin bahar niklo',
                'एडमिन लॉगआउट', 'साइन आउट'
            ],
        ],
    ];

    /**
     * Canonical Sidebar Sections with parent-child hierarchies and bilingual aliases.
     * Submenu parents have type 'section_parent' and NO direct destination URL.
     */
    protected static array $sidebarSections = [
        'member' => [
            'profile' => [
                'key' => 'profile',
                'name' => 'Profile',
                'panel' => 'member',
                'aliases' => [
                    'profile', 'profile section', 'my profile', 'meri profile', 'account', 'user summary',
                    'प्रोफाइल', 'मेरी प्रोफाइल', 'प्रोफाइल सेक्शन', 'अकाउंट'
                ],
                'pages' => [
                    'Edit Profile' => 'member_profile_summary',
                    'Change Password' => 'member_profile_security',
                    'Settings (Wallet Address)' => 'member_profile_wallet',
                ],
            ],
            'add_fund' => [
                'key' => 'add_fund',
                'name' => 'Add Fund',
                'panel' => 'member',
                'aliases' => [
                    'add fund', 'add funds', 'fund', 'funds', 'fund section', 'funds section', 'deposit', 'import fund', 'add fund section',
                    'ऐड फंड', 'ऐड फंड्स', 'फंड', 'फंड्स', 'फंड सेक्शन', 'डिपॉजिट', 'इम्पोर्ट फंड', 'फंड जोड़ो'
                ],
                'pages' => [
                    'Add Fund' => 'member_fund_import',
                    'Add Fund History' => 'member_fund_history',
                ],
            ],
            'investment' => [
                'key' => 'investment',
                'name' => 'Investment',
                'panel' => 'member',
                'aliases' => [
                    'investment', 'investments', 'investment section', 'staking', 'staking section', 'stake',
                    'इन्वेस्टमेंट', 'इन्वेस्टमेंट्स', 'इन्वेस्टमेंट सेक्शन', 'स्टेकिंग', 'स्टेकिंग सेक्शन', 'निवेश'
                ],
                'pages' => [
                    'Create Investment' => 'member_staking',
                    'Investment Details' => 'member_staking_details',
                ],
            ],
            'earnings' => [
                'key' => 'earnings',
                'name' => 'Earnings',
                'panel' => 'member',
                'aliases' => [
                    'earnings', 'earning', 'income', 'incomes', 'kamai', 'aamdani', 'earning section', 'earnings section', 'income section',
                    'अर्निंग', 'अर्निंग्स', 'इनकम', 'कमाई', 'आमदनी', 'अर्निंग सेक्शन', 'इनकम सेक्शन'
                ],
                'pages' => [
                    'Staking Income' => 'member_income_staking',
                    'Level Income' => 'member_income_level',
                    'Direct Income' => 'member_income_direct',
                ],
            ],
            'reward_section' => [
                'key' => 'reward_section',
                'name' => 'Reward Section',
                'panel' => 'member',
                'aliases' => [
                    'reward', 'rewards', 'reward section', 'rewards section', 'inam',
                    'रिवॉर्ड', 'रिवार्ड', 'रिवॉर्ड सेक्शन', 'रिवार्ड्स', 'इनाम'
                ],
                'pages' => [
                    'Reward Income' => 'member_income_reward',
                    'Reward Details' => 'member_income_reward_details',
                ],
            ],
            'team_management' => [
                'key' => 'team_management',
                'name' => 'Team Management',
                'panel' => 'member',
                'aliases' => [
                    'team', 'teams', 'team section', 'team management', 'network', 'downline', 'levels', 'tree',
                    'टीम', 'टीम सेक्शन', 'टीम मैनेजमेंट', 'नेटवर्क', 'डाउनलाइन', 'ट्री'
                ],
                'pages' => [
                    'Geneology' => 'member_team_geneology',
                    'Direct Members' => 'member_team_direct',
                    'Level Member Table' => 'member_team_level_table',
                ],
            ],
            'withdrawal_section' => [
                'key' => 'withdrawal_section',
                'name' => 'Withdrawal Section',
                'panel' => 'member',
                'aliases' => [
                    'withdrawal', 'withdrawals', 'wallet', 'wallets', 'nikasi', 'withdraw', 'withdrawal section', 'wallet section',
                    'withdrawl', 'withdrawl section',
                    'विड्रॉल', 'विथड्रॉल', 'विड्रोल', 'निकासी', 'वॉलेट', 'विड्रॉल सेक्शन', 'वॉलेट सेक्शन'
                ],
                'pages' => [
                    'Withdrawal Fund' => 'member_wallet_main',
                    'Withdrawal History' => 'member_wallet_history',
                    'Wallet to P2P Transfer' => 'member_wallet_to_p2p',
                    'Transaction History' => 'member_wallet_transactions',
                ],
            ],
            'p2p' => [
                'key' => 'p2p',
                'name' => 'P2P',
                'panel' => 'member',
                'aliases' => [
                    'p2p', 'p to p', 'p-to-p', 'p2p section', 'p to p section', 'peer to peer',
                    'पी टू पी', 'पी2पी', 'पी2पी सेक्शन', 'पी टू पी सेक्शन', 'petrol'
                ],
                'pages' => [
                    'P2P Wallet' => 'member_p2p_wallet',
                    'P2P History' => 'member_p2p_history',
                ],
            ],
            'support' => [
                'key' => 'support',
                'name' => 'Support',
                'panel' => 'member',
                'aliases' => [
                    'support', 'support section', 'help desk', 'ticket', 'tickets', 'help',
                    'सपोर्ट', 'सपोर्ट सेक्शन', 'हेल्प', 'टिकट'
                ],
                'pages' => [
                    'Create New Ticket' => 'member_support_create',
                    'Support Tickets' => 'member_support_tickets',
                ],
            ],
        ],

        'admin' => [
            'member_management' => [
                'key' => 'member_management',
                'name' => 'Member Management',
                'panel' => 'admin',
                'aliases' => [
                    'member management', 'members management', 'users', 'user management', 'users list', 'member section', 'all members', 'admin members',
                    'मेंबर मैनेजमेंट', 'मेंबर्स मैनेजमेंट', 'यूजर्स', 'यूजर मैनेजमेंट', 'मेंबर सेक्शन'
                ],
                'pages' => [
                    'Members Details' => 'admin_member_details',
                    'Account Control' => 'admin_account_control',
                    'Members Security' => 'admin_member_security',
                    'Zero Pin Members' => 'admin_free_pin_account',
                    'Member Wallet Address' => 'admin_wallet_address',
                ],
            ],
            'funds_management' => [
                'key' => 'funds_management',
                'name' => 'Funds Management',
                'panel' => 'admin',
                'aliases' => [
                    'funds management', 'fund management', 'funds', 'fund', 'admin funds', 'funds section',
                    'फंड्स मैनेजमेंट', 'फंड मैनेजमेंट', 'फंड्स', 'फंड', 'फंड्स सेक्शन'
                ],
                'pages' => [
                    'Add Funds' => 'admin_funds_add',
                    'Deduct Funds' => 'admin_funds_deduct',
                    'Funds Details' => 'admin_funds_add_details',
                    'Import Fund History' => 'admin_request_history',
                ],
            ],
            'staking_section' => [
                'key' => 'staking_section',
                'name' => 'Staking Section',
                'panel' => 'admin',
                'aliases' => [
                    'staking section', 'staking', 'stake', 'admin staking',
                    'स्टेकिंग सेक्शन', 'स्टेकिंग', 'एडमिन स्टेकिंग'
                ],
                'pages' => [
                    'Staking Income' => 'admin_staking_incomes',
                    'Staking Details' => 'admin_staking_details',
                ],
            ],
            'income_section' => [
                'key' => 'income_section',
                'name' => 'Income Section',
                'panel' => 'admin',
                'aliases' => [
                    'income section', 'incomes section', 'income', 'incomes', 'income management', 'admin income',
                    'इनकम सेक्शन', 'इनकम मैनेजमेंट', 'इनकम', 'एडमिन इनकम'
                ],
                'pages' => [
                    'Level Incomes' => 'admin_income_level',
                    'Reward Income' => 'admin_income_reward',
                    'Direct Income' => 'admin_income_direct',
                ],
            ],
            'payment_management' => [
                'key' => 'payment_management',
                'name' => 'Payment Management',
                'panel' => 'admin',
                'aliases' => [
                    'payment management', 'payments', 'payment', 'withdrawals', 'withdrawal', 'payouts', 'payment section',
                    'पेमेंट मैनेजमेंट', 'पेमेंट्स', 'पेमेंट', 'विड्रॉल्स', 'पेआउट्स', 'पेमेंट सेक्शन'
                ],
                'pages' => [
                    'New Withdrawal Requests' => 'admin_new_withdrawals',
                    'Cancelled Requests' => 'admin_cancelled_requests',
                    'Payment History' => 'admin_payment_history',
                ],
            ],
            'support' => [
                'key' => 'support',
                'name' => 'Support',
                'panel' => 'admin',
                'aliases' => [
                    'support', 'support section', 'tickets', 'ticket', 'admin support',
                    'सपोर्ट', 'सपोर्ट सेक्शन', 'टिकट्स', 'एडमिन सपोर्ट'
                ],
                'pages' => [
                    'New Support Tickets' => 'admin_support_new',
                    'Open Support Tickets' => 'admin_support_open',
                    'Closed Support Tickets' => 'admin_support_close',
                ],
            ],
        ],
    ];

    /**
     * Get all registered destinations.
     */
    public static function getAllDestinations(): array
    {
        return self::$destinations;
    }

    /**
     * Get a destination by its unique key.
     */
    public static function getDestination(string $key): ?array
    {
        return self::$destinations[$key] ?? null;
    }

    /**
     * Get all sidebar sections.
     */
    public static function getSidebarSections(): array
    {
        return self::$sidebarSections;
    }

    /**
     * Get sidebar sections for a specific portal ('member' or 'admin').
     */
    public static function getSectionsForPanel(string $panel): array
    {
        return self::$sidebarSections[$panel] ?? [];
    }

    /**
     * Get a specific sidebar section by portal and section key.
     */
    public static function getSection(string $panel, string $secKey): ?array
    {
        return self::$sidebarSections[$panel][$secKey] ?? null;
    }

    /**
     * Find a matching sidebar section using Unicode-aware boundary matching across English, Hinglish, and Devanagari.
     * Returns an array with ['key' => $secKey, 'data' => $secData, 'match_length' => $len] or null.
     */
    public static function findSectionByAlias(string $utterance, string $panel): ?array
    {
        $sections = self::getSectionsForPanel($panel);
        $clean = mb_strtolower(trim($utterance), 'UTF-8');

        $bestMatch = null;
        $longestAliasLen = 0;

        foreach ($sections as $secKey => $secData) {
            foreach ($secData['aliases'] as $alias) {
                $aliasLower = mb_strtolower(trim($alias), 'UTF-8');

                // Special handling for P2P 'petrol' phonetic misrecognition:
                // Only treat 'petrol' as P2P if surrounding context has section/wallet/transfer cues
                if ($aliasLower === 'petrol') {
                    if (!preg_match('/(?<![\p{L}\p{M}\p{N}])(?:wallet|section|history|transfer|me|mein|par)(?![\p{L}\p{M}\p{N}])/ui', $clean)) {
                        continue;
                    }
                }

                // Unicode-safe word boundary: checks that alias is not bordered by letters, marks, or numbers
                $pattern = '/(?<![\p{L}\p{M}\p{N}])' . preg_quote($aliasLower, '/') . '(?![\p{L}\p{M}\p{N}])/ui';

                if (preg_match($pattern, $clean)) {
                    $len = mb_strlen($aliasLower, 'UTF-8');
                    if ($len > $longestAliasLen) {
                        $bestMatch = [
                            'key' => $secKey,
                            'data' => $secData,
                            'match_length' => $len,
                            'matched_alias' => $aliasLower,
                        ];
                        $longestAliasLen = $len;
                    }
                }
            }
        }

        return $bestMatch;
    }

    /**
     * Find a matching child page within a section or across the active panel.
     */
    public static function findPageByAlias(string $utterance, ?string $panel = null): ?array
    {
        $clean = mb_strtolower(trim($utterance), 'UTF-8');
        $bestMatch = null;
        $longestAliasLen = 0;

        foreach (self::$destinations as $destKey => $dest) {
            // Filter by panel if specified
            if ($panel !== null && $dest['panel'] !== $panel && $dest['panel'] !== 'public') {
                continue;
            }

            foreach ($dest['aliases'] as $alias) {
                $aliasLower = mb_strtolower(trim($alias), 'UTF-8');
                $pattern = '/(?<![\p{L}\p{M}\p{N}])' . preg_quote($aliasLower, '/') . '(?![\p{L}\p{M}\p{N}])/ui';

                if (preg_match($pattern, $clean)) {
                    $len = mb_strlen($aliasLower, 'UTF-8');
                    if ($len > $longestAliasLen) {
                        $bestMatch = [
                            'key' => $destKey,
                            'data' => $dest,
                            'match_length' => $len,
                            'matched_alias' => $aliasLower,
                        ];
                        $longestAliasLen = $len;
                    }
                }
            }
        }

        return $bestMatch;
    }

    /**
     * Consistency check: verifies every canonical destination path against Laravel routes.
     * Returns an array of discrepancies if any are found.
     */
    public static function validateConsistency(): array
    {
        $discrepancies = [];
        $registeredRoutes = [];

        foreach (Route::getRoutes()->getRoutes() as $r) {
            if (in_array('GET', $r->methods(), true)) {
                $registeredRoutes[] = '/' . ltrim($r->uri(), '/');
            }
        }

        foreach (self::$destinations as $key => $dest) {
            $path = $dest['path'];
            // Normalize path for comparison
            $cleanPath = '/' . ltrim(explode('?', $path)[0], '/');
            if (!in_array($cleanPath, $registeredRoutes, true)) {
                $discrepancies[] = [
                    'key' => $key,
                    'path' => $path,
                    'error' => 'Path not found in Laravel registered GET routes',
                ];
            }
        }

        return $discrepancies;
    }
}
