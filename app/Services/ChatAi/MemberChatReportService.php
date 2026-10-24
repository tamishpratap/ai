<?php

namespace App\Services\ChatAi;

use App\Models\DailyIncome;
use App\Models\DirectIncome;
use App\Models\LevelIncome;
use App\Models\MemberDetail;
use App\Models\RewardIncome;
use App\Models\StakingDetail;
use App\Models\StakingIncome;
use App\Models\SupportTicket;
use App\Models\WalletTransfer;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Schema;

class MemberChatReportService
{
    /**
     * Normalize question for semantic matching.
     */
    public function normalizeQuestion(string $query): string
    {
        $clean = mb_strtolower($query, 'UTF-8');
        $clean = str_replace(['’', "'", '-', '_'], ' ', $clean);

        return trim((string) preg_replace('/[^\p{L}\p{N}\s\/]+/u', ' ', preg_replace('/\s+/u', ' ', $clean)));
    }

    /**
     * Detect user intent via keyword and Levenshtein token matching.
     */
    public function detectIntent(string $query): string
    {
        $keywords = [
            'overview' => ['summary', 'overview', 'sab kuch', 'poora data', 'pura data', 'complete detail', 'sari detail', 'saari detail', 'सारांश', 'पूरी जानकारी', 'सब कुछ'],
            'withdrawal' => ['withdrawal', 'withdraw', 'payout', 'nikasi', 'nikalna', 'cash out', 'विड्रॉल', 'निकासी', 'पैसे निकाले'],
            'tickets' => ['ticket', 'support', 'complaint', 'help request', 'शिकायत', 'सपोर्ट', 'टिकट'],
            'transactions' => ['transaction', 'wallet history', 'statement', 'ledger', 'len den', 'entry', 'ट्रांजैक्शन', 'लेन देन', 'स्टेटमेंट'],
            'activation' => ['activation', 'activate', 'joining', 'active kab', 'आईडी एक्टिव', 'एक्टिवेशन', 'जॉइनिंग'],
            'wallet' => ['wallet', 'balance', 'paisa', 'paise', 'available amount', 'वॉलेट', 'बैलेंस', 'पैसा', 'राशि'],
            'income' => ['income', 'earning', 'kamai', 'bonus', 'commission', 'profit', 'aamdani', 'इनकम', 'कमाई', 'आमदनी', 'बोनस'],
            'team' => ['downline', 'team', 'network', 'referral', 'direct member', 'left right', 'members', 'टीम', 'डाउनलाइन', 'रेफरल', 'सदस्य'],
            'investment' => ['investment', 'package', 'plan', 'business', 'biz', 'self business', 'team business', 'staking', 'stake', 'निवेश', 'पैकेज', 'बिजनेस', 'व्यापार'],
            'profile' => ['profile', 'account', 'status', 'kyc', 'member id', 'meri id', 'mera naam', 'email', 'mobile', 'bank', 'प्रोफाइल', 'खाता', 'मेरी आईडी', 'मेरा नाम', 'मोबाइल', 'बैंक'],
        ];

        $tokens = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $bestIntent = 'unknown';
        $bestScore = 0;

        foreach ($keywords as $intent => $phrases) {
            $score = 0;
            foreach ($phrases as $phrase) {
                if (str_contains($query, $phrase)) {
                    $score += str_contains($phrase, ' ') ? 6 : 4;
                    continue;
                }

                if (str_contains($phrase, ' ') || mb_strlen($phrase) < 4) {
                    continue;
                }

                foreach ($tokens as $token) {
                    if (!preg_match('/^[a-z0-9]+$/', $token) || !preg_match('/^[a-z0-9]+$/', $phrase)) {
                        continue;
                    }
                    $distance = levenshtein($token, $phrase);
                    $allowed = mb_strlen($phrase) >= 8 ? 2 : 1;
                    if ($distance <= $allowed) {
                        $score += 2;
                        break;
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIntent = $intent;
            }
        }

        return $bestScore >= 2 ? $bestIntent : 'overview';
    }

    /**
     * Parse relative or explicit dates from question.
     */
    public function parseDates(string $query, string $lowercaseQuery): array
    {
        $targetDate = null;
        $startDate = null;
        $endDate = null;

        if (str_contains($lowercaseQuery, 'today') || str_contains($lowercaseQuery, 'aaj') || str_contains($lowercaseQuery, 'आज')) {
            $targetDate = date('Y-m-d');
        } elseif (str_contains($lowercaseQuery, 'yesterday') || str_contains($lowercaseQuery, 'kal') || str_contains($lowercaseQuery, 'कल')) {
            $targetDate = date('Y-m-d', strtotime('yesterday'));
        }

        preg_match_all('~(\d{1,2})[-/](\d{1,2})[-/](\d{4})~', $query, $matches);
        if (!empty($matches[0])) {
            $dates = [];
            foreach ($matches[0] as $match) {
                $normalized = str_replace('/', '-', $match);
                $dates[] = date('Y-m-d', strtotime($normalized));
            }
            if (count($dates) === 1) {
                $targetDate = $dates[0];
            } elseif (count($dates) >= 2) {
                $startDate = $dates[0];
                $endDate = $dates[1];
            }
        }

        return [
            'targetDate' => $targetDate,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * Compile verified read-only database context for member.
     */
    public function compileContext(MemberDetail $member, string $intent, array $dateParams = []): array
    {
        $memberCode = (string) $member->memberid;

        if ($intent === 'overview') {
            $level = Schema::hasTable('level_incomes') ? (float) LevelIncome::where('memberid', $memberCode)->sum('amount') : 0;
            $direct = Schema::hasTable('direct_incomes') ? (float) DirectIncome::where('memberid', $memberCode)->sum('amount') : 0;
            $staking = Schema::hasTable('staking_incomes') ? (float) StakingIncome::where('memberid', $memberCode)->sum('amount') : 0;
            $daily = Schema::hasTable('daily_incomes')
                ? (float) DailyIncome::where(function ($q) use ($memberCode, $member) {
                    $q->where('member_id', $memberCode)->orWhere('member_id', (string) $member->id);
                })->sum('amount')
                : 0;
            $reward = Schema::hasTable('reward_incomes') ? (float) RewardIncome::where('memberid', $memberCode)->sum('amount') : 0;

            return [
                'type' => 'member_overview',
                'name' => $member->name,
                'member_id' => $memberCode,
                'status' => $member->status,
                'kyc_status' => $member->kyc_status ?: 'Unverified',
                'cash_wallet' => (float) $member->wallet,
                'p2p_wallet' => (float) $member->p2p_wallet,
                'active_package' => (float) $member->package,
                'package_name' => $member->package_name ?: 'Standard',
                'self_business' => (float) $member->self_biz,
                'team_business' => (float) $member->team_biz,
                'left_count' => (int) $member->left_count,
                'right_count' => (int) $member->right_count,
                'direct_referrals' => MemberDetail::where('sponsorid', $memberCode)->count(),
                'total_income' => $level + $direct + $staking + $daily + $reward,
                'sponsor_id' => $member->sponsorid ?: 'N/A',
                'activated_at' => $member->activated_at ?: $member->package_activated_at ?: 'N/A',
                'withdrawal_count' => Schema::hasTable('withdrawal_requests') ? WithdrawalRequest::where('memberid', $memberCode)->count() : 0,
                'support_ticket_count' => Schema::hasTable('support_tickets') ? SupportTicket::where('memberid', $memberCode)->count() : 0,
            ];
        }

        if ($intent === 'wallet') {
            return [
                'type' => 'wallet',
                'cash_wallet' => (float) $member->wallet,
                'p2p_wallet' => (float) $member->p2p_wallet,
                'coin_wallet' => (float) ($member->coin_wallet ?? 0),
                'total_balance' => (float) ($member->wallet + $member->p2p_wallet),
            ];
        }

        if ($intent === 'income') {
            $start = $dateParams['startDate'] ?? null ?: '1970-01-01';
            $end = $dateParams['endDate'] ?? null ?: date('Y-m-d');
            if (!empty($dateParams['targetDate'])) {
                $start = $dateParams['targetDate'];
                $end = $dateParams['targetDate'];
            }

            $startTs = $start . ' 00:00:00';
            $endTs = $end . ' 23:59:59';

            $level = Schema::hasTable('level_incomes')
                ? (float) LevelIncome::where('memberid', $memberCode)->whereBetween('created_at', [$startTs, $endTs])->sum('amount')
                : 0;
            $direct = Schema::hasTable('direct_incomes')
                ? (float) DirectIncome::where('memberid', $memberCode)->whereBetween('created_at', [$startTs, $endTs])->sum('amount')
                : 0;
            $staking = Schema::hasTable('staking_incomes')
                ? (float) StakingIncome::where('memberid', $memberCode)->whereBetween('created_at', [$startTs, $endTs])->sum('amount')
                : 0;
            $daily = Schema::hasTable('daily_incomes')
                ? (float) DailyIncome::where(function ($q) use ($memberCode, $member) {
                    $q->where('member_id', $memberCode)->orWhere('member_id', (string) $member->id);
                })->whereBetween('created_at', [$startTs, $endTs])->sum('amount')
                : 0;
            $reward = Schema::hasTable('reward_incomes')
                ? (float) RewardIncome::where('memberid', $memberCode)->whereBetween('created_at', [$startTs, $endTs])->sum('amount')
                : 0;

            return [
                'type' => 'income',
                'start_date' => $start,
                'end_date' => $end,
                'total_income' => (float) ($level + $direct + $staking + $daily + $reward),
                'breakdown' => [
                    'direct_sponsor' => (float) $direct,
                    'level_income' => (float) $level,
                    'staking_income' => (float) $staking,
                    'daily_roi' => (float) $daily,
                    'reward_income' => (float) $reward,
                ],
            ];
        }

        if ($intent === 'team') {
            return [
                'type' => 'team',
                'left_count' => (int) $member->left_count,
                'right_count' => (int) $member->right_count,
                'total_referrals' => MemberDetail::where('sponsorid', $memberCode)->count(),
            ];
        }

        if ($intent === 'investment') {
            $stakingRecords = Schema::hasTable('staking_details')
                ? StakingDetail::where('memberid', $memberCode)->latest()->limit(5)->get(['package_name', 'invest_amount', 'invest_date', 'status'])->toArray()
                : [];

            return [
                'type' => 'investment',
                'active_package' => (float) $member->package,
                'package_name' => $member->package_name ?: 'Standard',
                'self_business' => (float) $member->self_biz,
                'team_business' => (float) $member->team_biz,
                'staking_investments' => $stakingRecords,
            ];
        }

        if ($intent === 'withdrawal' || $intent === 'withdrawals') {
            $withdrawals = Schema::hasTable('withdrawal_requests')
                ? WithdrawalRequest::where('memberid', $memberCode)->latest()->limit(10)->get(['request_id', 'gross_amount', 'net_amount', 'status', 'request_date'])
                : collect();

            return [
                'type' => 'withdrawal',
                'total_requested' => (float) $withdrawals->sum('gross_amount'),
                'total_net_amount' => (float) $withdrawals->sum('net_amount'),
                'requests' => $withdrawals->toArray(),
            ];
        }

        if ($intent === 'transactions') {
            $transactions = Schema::hasTable('wallet_transfers')
                ? WalletTransfer::where('memberid', $memberCode)->latest()->limit(10)->get(['walletType', 'debit', 'credit', 'balance', 'particular', 'created_at'])
                : collect();

            return [
                'type' => 'wallet_transactions',
                'cash_wallet' => (float) $member->wallet,
                'p2p_wallet' => (float) $member->p2p_wallet,
                'recent_transactions' => $transactions->toArray(),
            ];
        }

        if ($intent === 'tickets' || $intent === 'support') {
            $tickets = Schema::hasTable('support_tickets')
                ? SupportTicket::where('memberid', $memberCode)->latest()->limit(10)->get(['ticket_id', 'subject', 'status', 'created_at'])
                : collect();

            return [
                'type' => 'support_tickets',
                'total_tickets' => $tickets->count(),
                'recent_tickets' => $tickets->toArray(),
            ];
        }

        if ($intent === 'activation') {
            return [
                'type' => 'activation',
                'account_status' => $member->status,
                'active_package' => (float) $member->package,
                'package_name' => $member->package_name ?: 'Standard',
                'activated_at' => $member->activated_at ?: $member->package_activated_at ?: 'N/A',
            ];
        }

        // Profile (default)
        return [
            'type' => 'profile',
            'name' => $member->name,
            'member_id' => $memberCode,
            'email' => $member->email,
            'mobile' => $member->mobile,
            'status' => $member->status,
            'rank' => $member->rank ?: 'N/A',
            'kyc_status' => $member->kyc_status ?: 'Unverified',
            'sponsor_id' => $member->sponsorid ?: 'N/A',
            'placement_id' => $member->placementid ?: 'N/A',
            'position' => $member->position ?: 'N/A',
            'country' => $member->country ?: 'N/A',
            'state' => $member->state ?: 'N/A',
            'city' => $member->city ?: 'N/A',
            'bank_name' => $member->bank ?: 'N/A',
            'account_holder' => $member->holder ?: 'N/A',
            'bank_account' => filled($member->account_no) ? 'XXXX' . substr((string) $member->account_no, -4) : 'N/A',
            'ifsc' => $member->ifsc ?: 'N/A',
            'wallet_address' => $member->wallet_address ?: 'N/A',
        ];
    }

    /**
     * Build deterministic source-style fallback responses when Gemini is unavailable.
     */
    public function buildFallbackResponse(string $intent, array $context): string
    {
        switch ($intent) {
            case 'overview':
                return "Aapke account ka complete summary:\n" .
                    "- Name: **{$context['name']}**\n" .
                    "- Member ID: **{$context['member_id']}**\n" .
                    "- Status / KYC: **{$context['status']} / {$context['kyc_status']}**\n" .
                    '- Cash Wallet: **₹' . number_format($context['cash_wallet'], 2) . "**\n" .
                    '- P2P Wallet: **₹' . number_format($context['p2p_wallet'], 2) . "**\n" .
                    '- Total Income: **₹' . number_format($context['total_income'], 2) . "**\n" .
                    '- Active Package: **' . $context['package_name'] . ' ($' . number_format($context['active_package'], 2) . ")**\n" .
                    '- Self Business: **₹' . number_format($context['self_business'], 2) . "**\n" .
                    '- Team Business: **₹' . number_format($context['team_business'], 2) . "**\n" .
                    '- Team: **' . ($context['left_count'] + $context['right_count']) . "** members, Direct Referrals: **{$context['direct_referrals']}**.";

            case 'wallet':
                return "Aapke account me current Cash Wallet Balance **₹" . number_format($context['cash_wallet'], 2) .
                    "** aur P2P Wallet Balance **₹" . number_format($context['p2p_wallet'], 2) .
                    "** hai. Aapka total available balance **₹" . number_format($context['total_balance'], 2) . "** hai.";

            case 'income':
                return "Aapko select ki gayi dates ({$context['start_date']} se {$context['end_date']}) ke beech total income **₹" . number_format($context['total_income'], 2) . "** mili hai.\n\nBreakdown:\n" .
                    '- Direct Sponsor Income: ₹' . number_format($context['breakdown']['direct_sponsor'], 2) . "\n" .
                    '- Level Income: ₹' . number_format($context['breakdown']['level_income'], 2) . "\n" .
                    '- Staking Cashback / ROI: ₹' . number_format($context['breakdown']['staking_income'], 2) . "\n" .
                    '- Daily Trading ROI: ₹' . number_format($context['breakdown']['daily_roi'], 2) . "\n" .
                    '- Reward Income: ₹' . number_format($context['breakdown']['reward_income'], 2) . '.';

            case 'team':
                return "Aapke total team members ki sankhya is prakar hai:\n" .
                    "- Left Count: **{$context['left_count']}** members\n" .
                    "- Right Count: **{$context['right_count']}** members\n" .
                    "- Direct Referrals: **{$context['total_referrals']}** members.";

            case 'investment':
                return "Aapka active investment package **{$context['package_name']} ($" . number_format($context['active_package'], 2) . ")** hai.\n" .
                    '- Self Business: ₹' . number_format($context['self_business'], 2) . "\n" .
                    '- Team Business: ₹' . number_format($context['team_business'], 2) . '.';

            case 'withdrawal':
            case 'withdrawals':
                $count = count($context['requests'] ?? []);
                return "Aapki ID ke recent withdrawal requests **{$count}** hain. Total requested **₹" . number_format($context['total_requested'] ?? 0, 2) . "** aur total net payout **₹" . number_format($context['total_net_amount'] ?? 0, 2) . "** hai.";

            case 'transactions':
                $count = count($context['recent_transactions'] ?? []);
                return "Cash Wallet **₹" . number_format($context['cash_wallet'] ?? 0, 2) . "**, P2P Wallet **₹" . number_format($context['p2p_wallet'] ?? 0, 2) . "** aur recent transactions count **{$count}** hai.";

            case 'tickets':
            case 'support':
                return "Aapki logged-in ID ke support tickets count **" . ($context['total_tickets'] ?? 0) . "** hai.";

            case 'activation':
                return "Aapka account status **{$context['account_status']}**, active package **{$context['package_name']} ($" . number_format($context['active_package'], 2) . ")** aur activation date **{$context['activated_at']}** hai.";

            case 'profile':
                return "### Profile Details\n" .
                    "- **Name**: {$context['name']}\n" .
                    "- **Member ID**: {$context['member_id']}\n" .
                    "- **Email**: {$context['email']}\n" .
                    "- **Mobile**: {$context['mobile']}\n" .
                    "- **Status**: {$context['status']}\n" .
                    "- **KYC Status**: {$context['kyc_status']}\n" .
                    "- **Wallet Address**: {$context['wallet_address']}.";

            default:
                return 'Main sirf aapke account ke data ki jankari de sakta hoon. Kripya apne wallet, income, downline ya profile se related sawal poochhein.';
        }
    }
}
