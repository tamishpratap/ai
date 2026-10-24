<?php

namespace App\Services\AI;

/**
 * IraFormRegistry
 *
 * Centralized, authoritative form schema registry for IRA Voice AI.
 * Defines eligible forms across Member, Admin, and Public interfaces in AI Demo.
 *
 * Each schema provides:
 * - Stable form key
 * - Authorized roles ('member', 'admin', 'guest')
 * - Route path identity & regex pattern
 * - Form title and purpose
 * - Scoped DOM form selector
 * - Field definitions with input types, constraints, aliases (En, Hinglish, Hi Devanagari)
 * - Select / radio allowed options and aliases
 * - Sensitive field tagging (passwords, PINs, OTPs, CAPTCHAs)
 * - Read-only and file-upload tagging
 * - Dynamic behaviors (AJAX lookups, cascading limit calculations)
 * - Risk classification ('critical', 'high', 'medium', 'low')
 */
class IraFormRegistry
{
    /**
     * Canonical schemas for all supported forms.
     */
    protected static array $schemas = [
        // =========================================================================
        // MEMBER PORTAL FORMS
        // =========================================================================

        'member_p2p_transfer' => [
            'key' => 'member_p2p_transfer',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/p2p-wallet',
            'title' => 'P2P Fund Transfer',
            'purpose' => 'Transfer funds from your P2P balance to another member',
            'selector' => 'form[action*="fundTrans"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'memberid' => [
                    'name' => 'memberid',
                    'selector' => '[name="memberid"], #fundmemberid',
                    'label' => 'Receiver Member ID',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'member id', 'memberid', 'recipient', 'receiver', 'receiver id', 'user id',
                        'kisko bhejna hai', 'kise bhejna hai', 'member', 'receiver member id',
                        'मेंबर आईडी', 'सदस्य आईडी', 'यूजर आईडी', 'प्राप्तकर्ता आईडी'
                    ],
                    'dynamic_events' => ['input', 'change'],
                    'dynamic_behavior' => 'Triggers /getMember AJAX lookup to fetch and display recipient full name',
                ],
                'amount' => [
                    'name' => 'amount',
                    'selector' => '[name="amount"], #transfer_amount',
                    'label' => 'Transfer Amount ($)',
                    'type' => 'number',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'amount', 'transfer amount', 'rashi', 'kitna bhejna hai', 'paise', 'dollars', 'rakam',
                        'रकम', 'राशि', 'अमाउंट', 'पैसे', 'डॉलर', 'ट्रांसफर अमाउंट'
                    ],
                    'dynamic_events' => ['input', 'change'],
                    'dynamic_behavior' => 'Triggers client-side wallet balance check',
                ],
                'txn_password' => [
                    'name' => 'txn_password',
                    'selector' => '[name="txn_password"], #inptxnpass',
                    'label' => 'Transaction Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'txn password', 'transaction password', 'pin', 'security pin', 'password',
                        'ट्रांजैक्शन पासवर्ड', 'पासवर्ड', 'पिन'
                    ],
                ],
            ],
        ],

        'member_wallet_to_p2p' => [
            'key' => 'member_wallet_to_p2p',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/wallet/wallet-to-p2p',
            'title' => 'Wallet to P2P Transfer',
            'purpose' => 'Transfer funds from Main Wallet to P2P Fund Wallet (5% service charge)',
            'selector' => 'form[action*="walletP2pTransfer"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'amount' => [
                    'name' => 'amount',
                    'selector' => '[name="amount"], #transfer_amount',
                    'label' => 'Transfer Amount ($)',
                    'type' => 'number',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'amount', 'transfer amount', 'rashi', 'dollars', 'rakam', 'kitna transfer karna hai',
                        'रकम', 'राशि', 'अमाउंट', 'पैसे', 'ट्रांसफर रकम'
                    ],
                    'dynamic_events' => ['input', 'change'],
                ],
                'pin' => [
                    'name' => 'pin',
                    'selector' => '[name="pin"]',
                    'label' => 'Security PIN',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'security pin', 'pin', 'transaction pin', 'txn password', 'password',
                        'सिक्योरिटी पिन', 'पिन', 'पासवर्ड'
                    ],
                ],
            ],
        ],

        'member_staking' => [
            'key' => 'member_staking',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/staking',
            'title' => 'Create Investment / Staking',
            'purpose' => 'Create a new staking investment package contract',
            'selector' => '#stakingForm, form[action*="createStaking"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'memberid' => [
                    'name' => 'memberid',
                    'selector' => '[name="memberid"], #memberid',
                    'label' => 'Member ID',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => true,
                    'file_upload' => false,
                    'aliases' => ['member id', 'memberid', 'मेंबर आईडी'],
                ],
                'stak_package' => [
                    'name' => 'stak_package',
                    'selector' => '[name="stak_package"], #stak_package',
                    'label' => 'Investment Package',
                    'type' => 'select',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'package', 'staking package', 'investment package', 'plan', 'tier',
                        'पैकेज', 'प्लान', 'स्टेकिंग पैकेज', 'निवेश पैकेज'
                    ],
                    'options' => [
                        [
                            'value' => 'Silver',
                            'label' => 'Silver ($100 - $1,000)',
                            'min_amount' => 100,
                            'max_amount' => 1000,
                            'aliases' => ['silver', 'silvar', 'chandi', 'सिल्वर', 'चांदी']
                        ],
                        [
                            'value' => 'Gold',
                            'label' => 'Gold ($1,100 - $5,000)',
                            'min_amount' => 1100,
                            'max_amount' => 5000,
                            'aliases' => ['gold', 'sona', 'golden', 'गोल्ड', 'सोना']
                        ],
                        [
                            'value' => 'Platinum',
                            'label' => 'Platinum ($5,100 - $10,000)',
                            'min_amount' => 5100,
                            'max_amount' => 10000,
                            'aliases' => ['platinum', 'platina', 'प्लैटिनम']
                        ],
                        [
                            'value' => 'Diamond',
                            'label' => 'Diamond ($10,100+)',
                            'min_amount' => 10100,
                            'max_amount' => null,
                            'aliases' => ['diamond', 'heera', 'डायमंड', 'हीरा']
                        ],
                    ],
                    'dynamic_events' => ['change'],
                    'dynamic_behavior' => 'Triggers setPackageLimit() to update min/max bounds on amount input',
                ],
                'amount' => [
                    'name' => 'amount',
                    'selector' => '[name="amount"], #amount',
                    'label' => 'Investment Amount ($)',
                    'type' => 'number',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'amount', 'investment amount', 'rashi', 'dollars', 'rakam',
                        'रकम', 'राशि', 'अमाउंट', 'निवेश रकम', 'डॉलर'
                    ],
                    'dynamic_events' => ['input', 'change'],
                    'dynamic_behavior' => 'Validates amount against selected package bounds ($100-$1000, $1100-$5000, etc.)',
                ],
                'txn_password' => [
                    'name' => 'txn_password',
                    'selector' => '[name="txn_password"]',
                    'label' => 'Transaction Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'transaction password', 'txn password', 'pin', 'password',
                        'ट्रांजैक्शन पासवर्ड', 'पासवर्ड'
                    ],
                ],
            ],
        ],

        'member_fund_import' => [
            'key' => 'member_fund_import',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/fund/import-fund',
            'title' => 'Import / Add Funds',
            'purpose' => 'Deposit funds into your wallet via payment gateway',
            'selector' => '#myForm, form[action*="instantFunds"]',
            'risk' => 'high',
            'voice_eligible' => true,
            'fields' => [
                'amount' => [
                    'name' => 'amount',
                    'selector' => '[name="amount"], #amount',
                    'label' => 'Deposit Amount ($)',
                    'type' => 'number',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'amount', 'deposit amount', 'add fund amount', 'paise', 'dollars', 'rashi', 'rakam',
                        'रकम', 'राशि', 'अमाउंट', 'डिपॉजिट रकम', 'डॉलर'
                    ],
                    'dynamic_events' => ['input', 'change'],
                    'dynamic_behavior' => 'Auto-calculates INR equivalent using live rate',
                ],
                'security_pin' => [
                    'name' => 'security_pin',
                    'selector' => '[name="security_pin"], #security_pin',
                    'label' => 'Security PIN',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'security pin', 'pin', 'transaction pin', 'password',
                        'सिक्योरिटी पिन', 'पिन'
                    ],
                ],
            ],
        ],

        'member_wallet_withdrawal' => [
            'key' => 'member_wallet_withdrawal',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/wallet',
            'title' => 'Main Wallet Withdrawal',
            'purpose' => 'Request withdrawal payout to your registered crypto wallet (Min $10)',
            'selector' => 'form[action*="initWithdrawal"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'amount' => [
                    'name' => 'amount',
                    'selector' => '[name="amount"], #amount',
                    'label' => 'Withdrawal Amount ($)',
                    'type' => 'number',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'amount', 'withdrawal amount', 'nikasi rashi', 'kitna nikalna hai', 'dollars', 'rakam',
                        'रकम', 'राशि', 'अमाउंट', 'निकासी रकम', 'विथड्रॉल अमाउंट'
                    ],
                    'dynamic_events' => ['input', 'change'],
                ],
                'txn_password' => [
                    'name' => 'txn_password',
                    'selector' => '[name="txn_password"]',
                    'label' => 'Transaction Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'transaction password', 'txn password', 'pin', 'password',
                        'ट्रांजैक्शन पासवर्ड', 'पासवर्ड'
                    ],
                ],
            ],
        ],

        'member_support_create' => [
            'key' => 'member_support_create',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/support/create-ticket',
            'title' => 'Create Support Ticket',
            'purpose' => 'Submit a support message or technical question to admin',
            'selector' => 'form[action*="createTicket"]',
            'risk' => 'low',
            'voice_eligible' => true,
            'fields' => [
                'memberid' => [
                    'name' => 'memberid',
                    'selector' => '[name="memberid"], #memberid',
                    'label' => 'Member ID',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => true,
                    'file_upload' => false,
                    'aliases' => ['member id', 'मेंबर आईडी'],
                ],
                'subject' => [
                    'name' => 'subject',
                    'selector' => '[name="subject"], #subject',
                    'label' => 'Subject',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'subject', 'topic', 'title', 'mudda', 'vishay', 'heading',
                        'विषय', 'सब्जेक्ट', 'टाइटल', 'मुद्दा'
                    ],
                    'dynamic_events' => ['input', 'change'],
                ],
                'issue' => [
                    'name' => 'issue',
                    'selector' => '[name="issue"], textarea[name="issue"]',
                    'label' => 'Message / Issue Details',
                    'type' => 'textarea',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'issue', 'message', 'problem', 'vivran', 'sandesh', 'details', 'dikkhat',
                        'विवरण', 'मैसेज', 'समस्या', 'दिक्कत', 'संदेश'
                    ],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'member_support_reply' => [
            'key' => 'member_support_reply',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/support/view-support-ticket',
            'title' => 'Reply Support Ticket',
            'purpose' => 'Send reply to an ongoing support ticket thread',
            'selector' => 'form[action*="MbReplyTicket"]',
            'risk' => 'low',
            'voice_eligible' => true,
            'fields' => [
                'reply' => [
                    'name' => 'reply',
                    'selector' => '[name="reply"], textarea[name="reply"]',
                    'label' => 'Your Reply',
                    'type' => 'textarea',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'reply', 'message', 'answer', 'jawaab', 'uttar', 'response',
                        'जवाब', 'उत्तर', 'मैसेज', 'रिप्लाई'
                    ],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'member_profile_user_summary' => [
            'key' => 'member_profile_user_summary',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/profile/user-summary',
            'title' => 'Edit Profile Details',
            'purpose' => 'Update your personal name, mobile number and country',
            'selector' => 'form[action*="insertProfile"]',
            'risk' => 'medium',
            'voice_eligible' => true,
            'fields' => [
                'name' => [
                    'name' => 'name',
                    'selector' => '[name="name"]',
                    'label' => 'Full Name',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['name', 'full name', 'naam', 'मेरा नाम', 'नाम', 'पूरा नाम'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'mobile' => [
                    'name' => 'mobile',
                    'selector' => '[name="mobile"]',
                    'label' => 'Mobile Number',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['mobile', 'phone', 'contact', 'mobile number', 'फ़ोन', 'मोबाइल'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'country' => [
                    'name' => 'country',
                    'selector' => '[name="country"]',
                    'label' => 'Country',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['country', 'desh', 'nation', 'देश', 'कंट्री'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'email' => [
                    'name' => 'email',
                    'selector' => '[name="email"]',
                    'label' => 'Email Address',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => true,
                    'file_upload' => false,
                    'aliases' => ['email', 'ईमेल'],
                ],
                'profile_image' => [
                    'name' => 'profile_image',
                    'selector' => '[name="profile_image"]',
                    'label' => 'Profile Image',
                    'type' => 'file',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => true,
                    'aliases' => ['profile image', 'photo', 'picture', 'image', 'फोटो', 'तस्वीर'],
                ],
            ],
        ],

        'member_profile_wallet_address' => [
            'key' => 'member_profile_wallet_address',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/profile/wallet-address',
            'title' => 'Wallet Address Setting',
            'purpose' => 'Save your USDT crypto address for withdrawal payouts',
            'selector' => 'form[action*="insertWalletAddress"]',
            'risk' => 'high',
            'voice_eligible' => true,
            'fields' => [
                'wallet_address' => [
                    'name' => 'wallet_address',
                    'selector' => '[name="wallet_address"]',
                    'label' => 'USDT Wallet Address',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'wallet address', 'address', 'crypto address', 'usdt address', 'trc20 address', 'bep20 address',
                        'वॉलेट एड्रेस', 'एड्रेस', 'क्रिप्टो एड्रेस'
                    ],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'member_security_txn_password' => [
            'key' => 'member_security_txn_password',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/profile/security',
            'title' => 'Change Transaction Password',
            'purpose' => 'Update or set your transaction password for withdrawals and transfers',
            'selector' => 'form[action*="txnpassChange"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'txn_password' => [
                    'name' => 'txn_password',
                    'selector' => 'form[action*="txnpassChange"] [name="txn_password"]',
                    'label' => 'New Transaction Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['new txn password', 'transaction password', 'नया ट्रांजैक्शन पासवर्ड'],
                ],
                'txn_password_confirm' => [
                    'name' => 'txn_password_confirm',
                    'selector' => 'form[action*="txnpassChange"] [name="txn_password_confirm"]',
                    'label' => 'Confirm Transaction Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['confirm transaction password', 'पुष्टि ट्रांजैक्शन पासवर्ड'],
                ],
            ],
        ],

        'member_security_login_password' => [
            'key' => 'member_security_login_password',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/profile/security',
            'title' => 'Change Account Password',
            'purpose' => 'Update your account login password',
            'selector' => 'form[action*="passChange"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'password' => [
                    'name' => 'password',
                    'selector' => 'form[action*="passChange"] [name="password"]',
                    'label' => 'New Login Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['new password', 'login password', 'नया पासवर्ड'],
                ],
                'password_confirm' => [
                    'name' => 'password_confirm',
                    'selector' => 'form[action*="passChange"] [name="password_confirm"]',
                    'label' => 'Confirm Login Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['confirm password', 'पुष्टि पासवर्ड'],
                ],
            ],
        ],

        'member_team_geneology_search' => [
            'key' => 'member_team_geneology_search',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/team/geneology',
            'title' => 'Genealogy Tree Search',
            'purpose' => 'Search and display the downline tree for any member ID',
            'selector' => 'form[action*="searchGenelogy"]',
            'risk' => 'low',
            'voice_eligible' => true,
            'fields' => [
                'searchid' => [
                    'name' => 'searchid',
                    'selector' => '[name="searchid"]',
                    'label' => 'Search Member ID',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'member id', 'search id', 'id', 'user id', 'target member',
                        'मेंबर आईडी', 'सर्च आईडी', 'सदस्य आईडी'
                    ],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        // =========================================================================
        // ADMIN PORTAL FORMS
        // =========================================================================

        'admin_funds_add' => [
            'key' => 'admin_funds_add',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/funds/add-funds',
            'title' => 'Add Member Funds',
            'purpose' => 'Credit funds manually to a member wallet',
            'selector' => 'form[action*="addMemFunds"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'memberid' => [
                    'name' => 'memberid',
                    'selector' => '[name="memberid"], #memberid',
                    'label' => 'Target Member ID',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['member id', 'memberid', 'user id', 'मेंबर आईडी', 'सदस्य आईडी'],
                    'dynamic_events' => ['input', 'change'],
                    'dynamic_behavior' => 'Triggers /getMember AJAX lookup to verify member name and status',
                ],
                'wallet_type' => [
                    'name' => 'wallet_type',
                    'selector' => '[name="wallet_type"]',
                    'label' => 'Wallet Type',
                    'type' => 'select',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['wallet type', 'wallet', 'वॉलेट टाइप', 'वॉलेट प्रकार'],
                    'options' => [
                        [
                            'value' => 'Fund Wallet',
                            'label' => 'P2P Wallet (Fund Wallet)',
                            'aliases' => ['p2p wallet', 'fund wallet', 'p2p', 'पी2पी वॉलेट', 'फंड वॉलेट']
                        ],
                        [
                            'value' => 'Wallet',
                            'label' => 'Main Wallet',
                            'aliases' => ['main wallet', 'wallet', 'normal wallet', 'मेन वॉलेट', 'वॉलेट']
                        ],
                    ],
                    'dynamic_events' => ['change'],
                ],
                'amount' => [
                    'name' => 'amount',
                    'selector' => '[name="amount"]',
                    'label' => 'Credit Amount ($)',
                    'type' => 'number',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['amount', 'dollars', 'rashi', 'rakam', 'अमाउंट', 'रकम', 'डॉलर'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'admin_funds_deduct' => [
            'key' => 'admin_funds_deduct',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/funds/deduct-funds',
            'title' => 'Deduct Member Funds',
            'purpose' => 'Debit funds manually from a member wallet',
            'selector' => 'form[action*="deductMemFunds"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'memberid' => [
                    'name' => 'memberid',
                    'selector' => '[name="memberid"], #memberid',
                    'label' => 'Target Member ID',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['member id', 'memberid', 'user id', 'मेंबर आईडी', 'सदस्य आईडी'],
                    'dynamic_events' => ['input', 'change'],
                    'dynamic_behavior' => 'Triggers /getMember AJAX lookup',
                ],
                'wallet_type' => [
                    'name' => 'wallet_type',
                    'selector' => '[name="wallet_type"]',
                    'label' => 'Wallet Type',
                    'type' => 'select',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['wallet type', 'wallet', 'वॉलेट टाइप'],
                    'options' => [
                        [
                            'value' => 'Fund Wallet',
                            'label' => 'P2P Wallet (Fund Wallet)',
                            'aliases' => ['p2p wallet', 'fund wallet', 'p2p', 'पी2पी वॉलेट', 'फंड वॉलेट']
                        ],
                        [
                            'value' => 'Wallet',
                            'label' => 'Main Wallet',
                            'aliases' => ['main wallet', 'wallet', 'मेन वॉलेट', 'वॉलेट']
                        ],
                    ],
                    'dynamic_events' => ['change'],
                ],
                'amount' => [
                    'name' => 'amount',
                    'selector' => '[name="amount"]',
                    'label' => 'Deduct Amount ($)',
                    'type' => 'number',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['amount', 'deduct amount', 'rashi', 'rakam', 'अमाउंट', 'कटौती रकम'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'admin_set_rates' => [
            'key' => 'admin_set_rates',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/rate/set-rates',
            'title' => 'Set USD Exchange Rate',
            'purpose' => 'Configure global USD to INR conversion rate for deposits',
            'selector' => 'form[action*="rateUpdate"]',
            'risk' => 'high',
            'voice_eligible' => true,
            'fields' => [
                'rate' => [
                    'name' => 'rate',
                    'selector' => '[name="rate"]',
                    'label' => 'USD Rate (INR)',
                    'type' => 'number',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['rate', 'dollar rate', 'exchange rate', 'inr rate', 'डॉलर रेट', 'रेट'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'admin_free_pin_account' => [
            'key' => 'admin_free_pin_account',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/zero-pin-members',
            'title' => 'Zero Pin Account Activation',
            'purpose' => 'Administratively activate an account under a package tier',
            'selector' => 'form[action*="PinUserAccount"]',
            'risk' => 'high',
            'voice_eligible' => true,
            'fields' => [
                'package' => [
                    'name' => 'package',
                    'selector' => '[name="package"], #package',
                    'label' => 'Package Tier',
                    'type' => 'select',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['package', 'tier', 'plan', 'पैकेज', 'प्लान'],
                    'options' => [
                        ['value' => 'Silver', 'label' => 'Silver ($100 - $1,000)', 'aliases' => ['silver', 'सिल्वर']],
                        ['value' => 'Gold', 'label' => 'Gold ($1,100 - $5,000)', 'aliases' => ['gold', 'गोल्ड']],
                        ['value' => 'Platinum', 'label' => 'Platinum ($5,100 - $10,000)', 'aliases' => ['platinum', 'प्लैटिनम']],
                        ['value' => 'Diamond', 'label' => 'Diamond ($10,100+)', 'aliases' => ['diamond', 'डायमंड']],
                    ],
                    'dynamic_events' => ['change'],
                ],
                'amount' => [
                    'name' => 'amount',
                    'selector' => '[name="amount"], #amount',
                    'label' => 'Activation Amount ($)',
                    'type' => 'number',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['amount', 'package amount', 'rashi', 'अमाउंट', 'रकम'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'admin_dash_messages' => [
            'key' => 'admin_dash_messages',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/dash-messages',
            'title' => 'Dashboard Broadcast Message',
            'purpose' => 'Post a marquee announcement message on all member dashboards',
            'selector' => 'form[action*="dashMsg"]',
            'risk' => 'medium',
            'voice_eligible' => true,
            'fields' => [
                'rank' => [
                    'name' => 'rank',
                    'selector' => '[name="rank"]',
                    'label' => 'Display Rank / Order',
                    'type' => 'number',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['rank', 'order', 'number', 'क्रमांक', 'रैंक'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'message' => [
                    'name' => 'message',
                    'selector' => '[name="message"]',
                    'label' => 'Broadcast Message',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['message', 'announcement', 'text', 'sandesh', 'संदेश', 'मैसेज'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'admin_wallet_address' => [
            'key' => 'admin_wallet_address',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/wallet-address',
            'title' => 'Admin Receiving Wallet Address',
            'purpose' => 'Set the official company crypto address to receive USDT member deposits',
            'selector' => 'form[action*="updateWallet"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'address' => [
                    'name' => 'address',
                    'selector' => '[name="address"]',
                    'label' => 'Company USDT Wallet Address',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['address', 'wallet address', 'usdt address', 'कंपनी एड्रेस', 'वॉलेट एड्रेस'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'admin_popup_image' => [
            'key' => 'admin_popup_image',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/popup-image',
            'title' => 'Popup Announcement Modal',
            'purpose' => 'Configure the login popup announcement modal',
            'selector' => 'form[action*="popupImageUpdate"]',
            'risk' => 'medium',
            'voice_eligible' => true,
            'fields' => [
                'title' => [
                    'name' => 'title',
                    'selector' => '[name="title"]',
                    'label' => 'Announcement Title',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['title', 'heading', 'शीर्षक', 'टाइटल'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'link' => [
                    'name' => 'link',
                    'selector' => '[name="link"]',
                    'label' => 'Action Link / URL',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['link', 'url', 'destination', 'वेबसाइट लिंक', 'लिंक'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'status' => [
                    'name' => 'status',
                    'selector' => '[name="status"]',
                    'label' => 'Announcement Status',
                    'type' => 'radio',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['status', 'state', 'स्थिति', 'स्टेटस'],
                    'options' => [
                        [
                            'value' => '1',
                            'label' => 'Active',
                            'aliases' => ['active', 'enable', 'chalu', 'on', 'सक्रिय', 'चालू']
                        ],
                        [
                            'value' => '0',
                            'label' => 'Inactive',
                            'aliases' => ['inactive', 'disable', 'band', 'off', 'निष्क्रिय', 'बंद']
                        ],
                    ],
                    'dynamic_events' => ['change'],
                ],
                'image' => [
                    'name' => 'image',
                    'selector' => '#popupImageInput, [name="image"]',
                    'label' => 'Popup Banner Image',
                    'type' => 'file',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => true,
                    'aliases' => ['image', 'banner', 'photo', 'फोटो', 'इमेज'],
                ],
            ],
        ],

        'admin_achievers_image' => [
            'key' => 'admin_achievers_image',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/achievers-image',
            'title' => 'Achievers Image Upload',
            'purpose' => 'Post achiever rank and photo to gallery',
            'selector' => 'form[action*="acheivImage"]',
            'risk' => 'medium',
            'voice_eligible' => true,
            'fields' => [
                'rank' => [
                    'name' => 'rank',
                    'selector' => '[name="rank"]',
                    'label' => 'Image Rank',
                    'type' => 'number',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['rank', 'क्रमांक', 'रैंक'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'title' => [
                    'name' => 'title',
                    'selector' => '[name="title"]',
                    'label' => 'Title',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['title', 'name', 'शीर्षक', 'टाइटल'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'image' => [
                    'name' => 'image',
                    'selector' => '[name="image"]',
                    'label' => 'Upload Image',
                    'type' => 'file',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => true,
                    'aliases' => ['image', 'photo', 'फोटो'],
                ],
            ],
        ],

        'admin_withdrawal_accept_comment' => [
            'key' => 'admin_withdrawal_accept_comment',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/withdrawal/withdrawal-comment',
            'title' => 'Withdrawal Accept with Transaction Hash',
            'purpose' => 'Approve payout and attach blockchain transaction hash',
            'selector' => 'form[action*="withdrawal.accept"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'transaction_hash' => [
                    'name' => 'transaction_hash',
                    'selector' => '[name="transaction_hash"]',
                    'label' => 'Blockchain Transaction Hash',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'transaction hash', 'hash', 'tx hash', 'blockchain hash',
                        'हैश', 'ट्रांजैक्शन हैश'
                    ],
                    'dynamic_events' => ['input', 'change'],
                ],
                'comment' => [
                    'name' => 'comment',
                    'selector' => 'form[action*="withdrawal.accept"] [name="comment"]',
                    'label' => 'Comment',
                    'type' => 'textarea',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['comment', 'note', 'remark', 'टिप्पणी', 'कमेंट'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'admin_withdrawal_cancel_comment' => [
            'key' => 'admin_withdrawal_cancel_comment',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/withdrawal/withdrawal-comment',
            'title' => 'Withdrawal Reject with Reason',
            'purpose' => 'Cancel withdrawal request and refund amount to member wallet',
            'selector' => 'form[action*="withdrawal.cancel"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'comment' => [
                    'name' => 'comment',
                    'selector' => 'form[action*="withdrawal.cancel"] [name="comment"]',
                    'label' => 'Rejection Reason / Comment',
                    'type' => 'textarea',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['comment', 'reason', 'rejection reason', 'karan', 'कारण', 'कमेंट'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'admin_date_filter' => [
            'key' => 'admin_date_filter',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => 'admin/*',
            'title' => 'History Date Filter',
            'purpose' => 'Filter history and report tables by quick preset or date range',
            'selector' => '#advancedDateFilterForm',
            'risk' => 'low',
            'voice_eligible' => true,
            'fields' => [
                'preset' => [
                    'name' => 'preset',
                    'selector' => '#adv_preset, [name="preset"]',
                    'label' => 'Period Preset',
                    'type' => 'select',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'preset', 'filter', 'period', 'range', 'quick period', 'time range',
                        'समय सीमा', 'फ़िल्टर', 'प्रीसेट'
                    ],
                    'options' => [
                        ['value' => 'today', 'label' => 'Today', 'aliases' => ['today', 'aaj', 'आज']],
                        ['value' => 'yesterday', 'label' => 'Yesterday', 'aliases' => ['yesterday', 'kal', 'beeta kal', 'कल', 'बीता कल']],
                        ['value' => 'last_7_days', 'label' => 'Last 7 Days', 'aliases' => ['last 7 days', 'pichle 7 din', 'one week', 'ek hafta', 'पिछले 7 दिन']],
                        ['value' => 'last_30_days', 'label' => 'Last 30 Days', 'aliases' => ['last 30 days', 'pichle 30 din', 'one month', 'ek mahina', 'पिछले 30 दिन']],
                        ['value' => 'this_month', 'label' => 'This Month', 'aliases' => ['this month', 'is mahine', 'current month', 'इस महीने']],
                        ['value' => 'last_month', 'label' => 'Last Month', 'aliases' => ['last month', 'pichla mahina', 'previous month', 'पिछले महीने']],
                        ['value' => 'this_year', 'label' => 'This Year', 'aliases' => ['this year', 'is saal', 'current year', 'इस साल']],
                        ['value' => 'custom', 'label' => 'Custom Range', 'aliases' => ['custom', 'custom range', 'manchaha', 'कस्टम']],
                    ],
                    'dynamic_events' => ['change'],
                    'dynamic_behavior' => 'Selecting preset automatically calculates and populates from_date and to_date',
                ],
                'from_date' => [
                    'name' => 'from_date',
                    'selector' => '#adv_from_date, [name="from_date"]',
                    'label' => 'From Date',
                    'type' => 'date',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['from date', 'start date', 'kab se', 'shuruat', 'दिनांक से', 'शुरुआती तारीख'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'to_date' => [
                    'name' => 'to_date',
                    'selector' => '#adv_to_date, [name="to_date"]',
                    'label' => 'To Date',
                    'type' => 'date',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['to date', 'end date', 'kab tak', 'aakhri date', 'दिनांक तक', 'अंतिम तारीख'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'admin_support_reply' => [
            'key' => 'admin_support_reply',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/support/view-support-ticket',
            'title' => 'Admin Support Reply',
            'purpose' => 'Send reply to member support ticket thread',
            'selector' => 'form[action*="Ticket"]',
            'risk' => 'low',
            'voice_eligible' => true,
            'fields' => [
                'reply' => [
                    'name' => 'reply',
                    'selector' => '[name="reply"]',
                    'label' => 'Reply Message',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['reply', 'message', 'answer', 'jawaab', 'जवाब', 'मैसेज', 'रिप्लाई'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'admin_member_update' => [
            'key' => 'admin_member_update',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/members/member-update',
            'title' => 'Member Profile Update (Admin Override)',
            'purpose' => 'Administratively edit personal profile details of a member',
            'selector' => 'form[action*="updateDetails"]',
            'risk' => 'high',
            'voice_eligible' => true,
            'fields' => [
                'name' => [
                    'name' => 'name',
                    'selector' => '[name="name"]',
                    'label' => 'Member Name',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['name', 'naam', 'नाम'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'email' => [
                    'name' => 'email',
                    'selector' => '[name="email"]',
                    'label' => 'Email',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['email', 'ईमेल'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'mobile' => [
                    'name' => 'mobile',
                    'selector' => '[name="mobile"]',
                    'label' => 'Mobile',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['mobile', 'phone', 'मोबाइल'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'father' => [
                    'name' => 'father',
                    'selector' => '[name="father"]',
                    'label' => 'Father Name',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['father', 'father name', 'पिता का नाम'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'address' => [
                    'name' => 'address',
                    'selector' => '[name="address"]',
                    'label' => 'Address',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['address', 'pata', 'पता'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'city' => [
                    'name' => 'city',
                    'selector' => '[name="city"]',
                    'label' => 'City',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['city', 'shahar', 'शहर'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'state' => [
                    'name' => 'state',
                    'selector' => '[name="state"]',
                    'label' => 'State',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['state', 'rajya', 'राज्य'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'country' => [
                    'name' => 'country',
                    'selector' => '[name="country"]',
                    'label' => 'Country',
                    'type' => 'text',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['country', 'desh', 'देश'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'password' => [
                    'name' => 'password',
                    'selector' => '[name="password"]',
                    'label' => 'Password',
                    'type' => 'password',
                    'required' => false,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['password', 'पासवर्ड'],
                ],
                'txn_password' => [
                    'name' => 'txn_password',
                    'selector' => '[name="txn_password"]',
                    'label' => 'Transaction Password',
                    'type' => 'password',
                    'required' => false,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['txn password', 'transaction password', 'ट्रांजैक्शन पासवर्ड'],
                ],
            ],
        ],

        'admin_member_security' => [
            'key' => 'admin_member_security',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/member-security',
            'title' => 'Member Security Reset',
            'purpose' => 'Reset passwords for a member directly',
            'selector' => 'form[action*="securityUpdate"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'password' => [
                    'name' => 'password',
                    'selector' => '[name="password"]',
                    'label' => 'New Password',
                    'type' => 'password',
                    'required' => false,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['password', 'new password', 'पासवर्ड'],
                ],
                'txnpassword' => [
                    'name' => 'txnpassword',
                    'selector' => '[name="txnpassword"]',
                    'label' => 'New Transaction Password',
                    'type' => 'password',
                    'required' => false,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['transaction password', 'txn password', 'ट्रांजैक्शन पासवर्ड'],
                ],
            ],
        ],

        // =========================================================================
        // PUBLIC / AUTHENTICATION FORMS
        // =========================================================================

        'public_member_signup' => [
            'key' => 'public_member_signup',
            'portal' => 'public',
            'roles' => ['guest', 'member', 'admin'],
            'path' => '/signup',
            'title' => 'Member Registration',
            'purpose' => 'Create a new member account under a sponsor',
            'selector' => '#signupForm, form[action*="userRegister"]',
            'risk' => 'medium',
            'voice_eligible' => true,
            'fields' => [
                'sponsor' => [
                    'name' => 'sponsor',
                    'selector' => '[name="sponsor"], #sponsor',
                    'label' => 'Sponsor ID',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => [
                        'sponsor', 'sponsor id', 'referral', 'sponser', 'upline',
                        'स्पॉन्सर आईडी', 'रेफरल आईडी', 'सदस्य आईडी'
                    ],
                    'dynamic_events' => ['input', 'change'],
                    'dynamic_behavior' => 'Triggers /getSponname AJAX lookup to display sponsor name',
                ],
                'name' => [
                    'name' => 'name',
                    'selector' => '[name="name"], #signup-name',
                    'label' => 'Full Name',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['name', 'full name', 'naam', 'नाम', 'पूरा नाम'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'email' => [
                    'name' => 'email',
                    'selector' => '[name="email"], #signin-username',
                    'label' => 'Email Address',
                    'type' => 'email',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['email', 'email address', 'ईमेल'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'mobile' => [
                    'name' => 'mobile',
                    'selector' => '[name="mobile"], #signup-mobile',
                    'label' => 'Mobile Number',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['mobile', 'phone', 'mobile number', 'contact', 'फ़ोन', 'मोबाइल नंबर'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'country' => [
                    'name' => 'country',
                    'selector' => '[name="country"]',
                    'label' => 'Country',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['country', 'desh', 'nation', 'देश', 'कंट्री'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'password' => [
                    'name' => 'password',
                    'selector' => '[name="password"], #signup-password',
                    'label' => 'Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['password', 'पासवर्ड'],
                ],
                'confirm_password' => [
                    'name' => 'confirm_password',
                    'selector' => '[name="confirm_password"], #confirm-password',
                    'label' => 'Confirm Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['confirm password', 'पुष्टि पासवर्ड'],
                ],
            ],
        ],

        'public_member_signin' => [
            'key' => 'public_member_signin',
            'portal' => 'public',
            'roles' => ['guest', 'member', 'admin'],
            'path' => '/member',
            'title' => 'Member Sign In',
            'purpose' => 'Authenticate into member portal',
            'selector' => 'form[action*="userLogin"]',
            'risk' => 'medium',
            'voice_eligible' => true,
            'fields' => [
                'memberid' => [
                    'name' => 'memberid',
                    'selector' => '[name="memberid"], #signin-username',
                    'label' => 'Member ID',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['member id', 'memberid', 'user id', 'username', 'आईडी', 'मेंबर आईडी'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'password' => [
                    'name' => 'password',
                    'selector' => '[name="password"], #signin-password',
                    'label' => 'Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['password', 'पासवर्ड'],
                ],
                'login-captcha' => [
                    'name' => 'login-captcha',
                    'selector' => '[name="login-captcha"], #login-captcha',
                    'label' => 'Math Captcha',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['captcha', 'math captcha', 'कैप्चा'],
                ],
            ],
        ],

        'public_admin_login' => [
            'key' => 'public_admin_login',
            'portal' => 'public',
            'roles' => ['guest', 'member', 'admin'],
            'path' => '/admin',
            'title' => 'Administrator Sign In',
            'purpose' => 'Authenticate into administrator portal',
            'selector' => 'form[action*="adminLogin"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'email' => [
                    'name' => 'email',
                    'selector' => '[name="email"], #email',
                    'label' => 'Admin Email Address',
                    'type' => 'email',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['email', 'admin email', 'ईमेल'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'password' => [
                    'name' => 'password',
                    'selector' => '[name="password"], #password',
                    'label' => 'Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['password', 'पासवर्ड'],
                ],
            ],
        ],

        'public_member_forget_password' => [
            'key' => 'public_member_forget_password',
            'portal' => 'public',
            'roles' => ['guest', 'member'],
            'path' => '/member-forget-password',
            'title' => 'Member Password Recovery',
            'purpose' => 'Send account recovery password to registered email address',
            'selector' => 'form[action*="memretrivePassword"]',
            'risk' => 'medium',
            'voice_eligible' => true,
            'fields' => [
                'email' => [
                    'name' => 'email',
                    'selector' => '[name="email"], #signin-username',
                    'label' => 'Registered Email Address',
                    'type' => 'email',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['email', 'registered email', 'email address', 'मेल', 'ईमेल'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'admin_profile_email' => [
            'key' => 'admin_profile_email',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/profile',
            'title' => 'Admin Profile Email Update',
            'purpose' => 'Update administrator contact and recovery email address',
            'selector' => 'form[action*="changeEmail"]',
            'risk' => 'high',
            'voice_eligible' => true,
            'fields' => [
                'email' => [
                    'name' => 'email',
                    'selector' => '[name="email"], #exampleInputName1',
                    'label' => 'New Administrator Email',
                    'type' => 'email',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['email', 'new email', 'admin email', 'ईमेल'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'member_security_email' => [
            'key' => 'member_security_email',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/profile/security',
            'title' => 'Update Account Email',
            'purpose' => 'Update registered email address for member account',
            'selector' => 'form[action*="emailChange"]',
            'risk' => 'high',
            'voice_eligible' => true,
            'fields' => [
                'email' => [
                    'name' => 'email',
                    'selector' => '[name="email"]',
                    'label' => 'New Email Address',
                    'type' => 'email',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['email', 'new email', 'email address', 'ईमेल'],
                    'dynamic_events' => ['input', 'change'],
                ],
            ],
        ],

        'member_account_activation' => [
            'key' => 'member_account_activation',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/account/activation',
            'title' => 'Account Activation',
            'purpose' => 'Activate member account with available wallet balance',
            'selector' => 'form[action*="userCreate"], form[action*="activateUser"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'memberid' => [
                    'name' => 'memberid',
                    'selector' => '#memberid, [name="memberid"]',
                    'label' => 'Member ID',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => true,
                    'file_upload' => false,
                    'aliases' => ['member id', 'memberid', 'user id'],
                ],
                'balance' => [
                    'name' => 'balance',
                    'selector' => '#basic-default-dob, [name="balance"]',
                    'label' => 'Available Balance',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => true,
                    'file_upload' => false,
                    'aliases' => ['balance', 'available balance', 'wallet balance'],
                ],
                'txn_password' => [
                    'name' => 'txn_password',
                    'selector' => '[name="txn_password"]',
                    'label' => 'Transaction Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['transaction password', 'txn password', 'pin', 'transaction pin'],
                ],
            ],
        ],

        'member_account_upgrade' => [
            'key' => 'member_account_upgrade',
            'portal' => 'member',
            'roles' => ['member'],
            'path' => '/member/account/upgrade',
            'title' => 'Upgrade Account Activation',
            'purpose' => 'Upgrade member tier using wallet balance',
            'selector' => 'form[action*="userCreate"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'memberid' => [
                    'name' => 'memberid',
                    'selector' => '#memberid, [name="memberid"]',
                    'label' => 'Member ID',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => true,
                    'file_upload' => false,
                    'aliases' => ['member id', 'memberid'],
                ],
                'balance' => [
                    'name' => 'balance',
                    'selector' => '#basic-default-dob, [name="balance"]',
                    'label' => 'Available Balance',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => true,
                    'file_upload' => false,
                    'aliases' => ['balance', 'available balance'],
                ],
                'txn_password' => [
                    'name' => 'txn_password',
                    'selector' => '[name="txn_password"]',
                    'label' => 'Transaction Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['transaction password', 'txn password', 'pin'],
                ],
            ],
        ],

        'public_admin_forget_password' => [
            'key' => 'public_admin_forget_password',
            'portal' => 'public',
            'roles' => ['guest', 'admin'],
            'path' => '/admin/forget-password',
            'title' => 'Admin Forget Password',
            'purpose' => 'Trigger administrator password recovery email',
            'selector' => 'form[action*="retrivePassword"]',
            'risk' => 'high',
            'voice_eligible' => false,
            'fields' => [
                'action' => [
                    'name' => 'action',
                    'selector' => 'button[type="submit"]',
                    'label' => 'Retrieve Password Action',
                    'type' => 'button',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => true,
                    'file_upload' => false,
                    'aliases' => ['retrieve password', 'reset password', 'पासवर्ड रीसेट'],
                ],
            ],
        ],

        'admin_profile_password' => [
            'key' => 'admin_profile_password',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/profile',
            'title' => 'Admin Profile Password Update',
            'purpose' => 'Change administrative login password',
            'selector' => 'form[action*="changePassword"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'password' => [
                    'name' => 'password',
                    'selector' => '[name="password"]',
                    'label' => 'Enter Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['password', 'new password', 'पासवर्ड'],
                ],
                'password_confirmation' => [
                    'name' => 'password_confirmation',
                    'selector' => '[name="password_confirmation"]',
                    'label' => 'Retype Password',
                    'type' => 'password',
                    'required' => true,
                    'sensitive' => true,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['confirm password', 'retype password', 'password confirmation'],
                ],
            ],
        ],

        'admin_deposit_details_bank' => [
            'key' => 'admin_deposit_details_bank',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/deposit/deposit-details',
            'title' => 'Deposit Bank & UPI Details',
            'purpose' => 'Update bank and UPI accounts for receiving member deposits',
            'selector' => 'form[action*="depositData"]',
            'risk' => 'high',
            'voice_eligible' => true,
            'fields' => [
                'bank' => [
                    'name' => 'bank',
                    'selector' => '[name="bank"]',
                    'label' => 'Bank Name',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['bank', 'bank name', 'bank ka naam', 'बैंक का नाम'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'ifsc' => [
                    'name' => 'ifsc',
                    'selector' => '[name="ifsc"]',
                    'label' => 'IFSC Code',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['ifsc', 'ifsc code', 'आईएफएससी'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'account_no' => [
                    'name' => 'account_no',
                    'selector' => '[name="account_no"]',
                    'label' => 'Account No',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['account number', 'account no', 'khata number', 'खाता संख्या'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'upi' => [
                    'name' => 'upi',
                    'selector' => '[name="upi"]',
                    'label' => 'UPI ID',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['upi', 'upi id', 'vpa', 'यूपीआई'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'qrcode' => [
                    'name' => 'qrcode',
                    'selector' => '[name="qrcode"]',
                    'label' => 'QR Code Image',
                    'type' => 'file',
                    'required' => false,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => true,
                    'aliases' => ['qr code', 'upi qr code', 'क्यूआर कोड'],
                ],
            ],
        ],

        'admin_deposit_details_usdt' => [
            'key' => 'admin_deposit_details_usdt',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/deposit/deposit-details',
            'title' => 'Deposit USDT Wallet Details',
            'purpose' => 'Update USDT receiving wallet and QR code',
            'selector' => 'form[action*="depositDataUSDT"]',
            'risk' => 'high',
            'voice_eligible' => true,
            'fields' => [
                'wallet' => [
                    'name' => 'wallet',
                    'selector' => '[name="wallet"]',
                    'label' => 'Wallet Address',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => false,
                    'aliases' => ['wallet', 'wallet address', 'usdt address', 'वॉलेट एड्रेस'],
                    'dynamic_events' => ['input', 'change'],
                ],
                'uqrcode' => [
                    'name' => 'uqrcode',
                    'selector' => '[name="uqrcode"]',
                    'label' => 'QR Code Image',
                    'type' => 'file',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => false,
                    'file_upload' => true,
                    'aliases' => ['qr code', 'usdt qr code', 'क्यूआर कोड'],
                ],
            ],
        ],

        'admin_withdrawal_online_pay' => [
            'key' => 'admin_withdrawal_online_pay',
            'portal' => 'admin',
            'roles' => ['admin'],
            'path' => '/admin/withdrawal/withdrawal-accept/*',
            'title' => 'Withdrawal Direct Online Pay Request',
            'purpose' => 'Trigger blockchain automatic payout for member withdrawal',
            'selector' => 'form[action*="sendWdlRequest"]',
            'risk' => 'critical',
            'voice_eligible' => true,
            'fields' => [
                'memberid' => [
                    'name' => 'memberid',
                    'selector' => '[name="memberid"]',
                    'label' => 'Member ID',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => true,
                    'file_upload' => false,
                    'aliases' => ['member id', 'memberid'],
                ],
                'wallet' => [
                    'name' => 'wallet',
                    'selector' => '[name="wallet"]',
                    'label' => 'Wallet Address',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => true,
                    'file_upload' => false,
                    'aliases' => ['wallet', 'wallet address'],
                ],
                'net_amount' => [
                    'name' => 'net_amount',
                    'selector' => '[name="net_amount"]',
                    'label' => 'Net Amount ($)',
                    'type' => 'text',
                    'required' => true,
                    'sensitive' => false,
                    'read_only' => true,
                    'file_upload' => false,
                    'aliases' => ['net amount', 'amount'],
                ],
            ],
        ],
    ];

    /**
     * Retrieve all registered form schemas.
     */
    public static function getAllForms(): array
    {
        return static::$schemas;
    }

    /**
     * Retrieve a specific form schema by its unique key.
     */
    public static function getForm(string $key): ?array
    {
        return static::$schemas[$key] ?? null;
    }

    /**
     * Find registered forms associated with a URL path or path prefix.
     * Enforces server-authoritative role access filtering.
     */
    public static function findFormsByPath(string $path, string $role = 'guest'): array
    {
        $normalizedPath = '/' . ltrim(parse_url($path, PHP_URL_PATH) ?? $path, '/');
        $matched = [];

        foreach (static::$schemas as $key => $schema) {
            // Role authorization check
            if (!in_array($role, $schema['roles'], true) && !in_array('guest', $schema['roles'], true)) {
                continue;
            }

            $schemaPath = '/' . ltrim($schema['path'], '/');

            // Wildcard matching (e.g. '/admin/*')
            if (str_ends_with($schemaPath, '/*')) {
                $prefix = rtrim(substr($schemaPath, 0, -2), '/');
                if (str_starts_with($normalizedPath, $prefix)) {
                    $matched[$key] = $schema;
                    continue;
                }
            }

            // Exact path match
            if ($normalizedPath === $schemaPath) {
                $matched[$key] = $schema;
                continue;
            }

            // Dynamic route parameterized match (e.g. '/admin/withdrawal/withdrawal-comment/{id}')
            $cleanBase = explode('{', $schemaPath)[0];
            if ($cleanBase !== '/' && str_starts_with($normalizedPath, rtrim($cleanBase, '/'))) {
                $matched[$key] = $schema;
            }
        }

        return $matched;
    }

    /**
     * Determine if a user role is permitted to interact with the given form.
     */
    public static function isFormAuthorized(string $formKey, string $role): bool
    {
        $schema = static::getForm($formKey);
        if (!$schema) {
            return false;
        }

        return in_array($role, $schema['roles'], true) || in_array('guest', $schema['roles'], true);
    }

    /**
     * Retrieve field definition within a form schema.
     */
    public static function getField(string $formKey, string $fieldKey): ?array
    {
        $schema = static::getForm($formKey);
        if (!$schema || !isset($schema['fields'][$fieldKey])) {
            return null;
        }

        return $schema['fields'][$fieldKey];
    }

    /**
     * Resolve spoken value to a valid option in a select or radio field.
     * Performs fuzzy matching against value, label, and localized aliases.
     *
     * @return array|null Returns ['value' => ..., 'label' => ...] or null if no match.
     */
    public static function resolveOption(string $formKey, string $fieldKey, string $spokenValue): ?array
    {
        $field = static::getField($formKey, $fieldKey);
        if (!$field || empty($field['options'])) {
            return null;
        }

        $clean = mb_strtolower(trim($spokenValue), 'UTF-8');
        // Strip common filler words
        $clean = preg_replace('/\b(select|chuno|kar do|karo|chahiye|wala|package|option)\b/iu', '', $clean);
        $clean = trim($clean);

        if ($clean === '') {
            return null;
        }

        // 1. Exact match on value or label
        foreach ($field['options'] as $opt) {
            if (mb_strtolower((string) $opt['value'], 'UTF-8') === $clean) {
                return ['value' => $opt['value'], 'label' => $opt['label']];
            }
            if (mb_strtolower((string) $opt['label'], 'UTF-8') === $clean) {
                return ['value' => $opt['value'], 'label' => $opt['label']];
            }
        }

        // 2. Match against registered aliases
        foreach ($field['options'] as $opt) {
            if (!empty($opt['aliases'])) {
                foreach ($opt['aliases'] as $alias) {
                    $lowerAlias = mb_strtolower($alias, 'UTF-8');
                    if ($lowerAlias === $clean || str_contains($clean, $lowerAlias) || str_contains($lowerAlias, $clean)) {
                        return ['value' => $opt['value'], 'label' => $opt['label']];
                    }
                }
            }
        }

        // 3. Substring matching in label
        foreach ($field['options'] as $opt) {
            $lowerLabel = mb_strtolower((string) $opt['label'], 'UTF-8');
            if (str_contains($lowerLabel, $clean) || str_contains($clean, mb_strtolower((string) $opt['value'], 'UTF-8'))) {
                return ['value' => $opt['value'], 'label' => $opt['label']];
            }
        }

        return null;
    }
}
