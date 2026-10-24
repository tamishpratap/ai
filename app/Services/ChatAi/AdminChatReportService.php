<?php

namespace App\Services\ChatAi;

use App\Models\DailyIncome;
use App\Models\DirectIncome;
use App\Models\FundRequest;
use App\Models\LevelIncome;
use App\Models\MemberDetail;
use App\Models\RewardIncome;
use App\Models\StakingDetail;
use App\Models\StakingIncome;
use App\Models\SupportTicket;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Schema;

class AdminChatReportService
{
    /**
     * Check if the query explicitly mentions a formatted member ID pattern.
     */
    public function hasExplicitMemberPattern(string $query): bool
    {
        return (bool) preg_match('~\b([a-z]{2,}\d+|\d{4,})\b~i', $query);
    }

    /**
     * Resolve a specific MemberDetail model if mentioned in query.
     */
    public function resolveMemberCandidate(string $query): ?MemberDetail
    {
        if (preg_match('~\b([a-z]{2,}\d+|\d{4,})\b~i', $query, $matches)) {
            $candidate = strtoupper($matches[1]);
            $member = MemberDetail::where('memberid', $candidate)->first();
            if ($member) {
                return $member;
            }
        }

        // Secondary fallback token matching
        $words = preg_split('~[\s,.:?!\-\#\(\)]+~', $query);
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) >= 3) {
                $found = MemberDetail::where('memberid', strtoupper($word))->first();
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Determine query intent and scope based on keywords and whether member was resolved.
     */
    public function detectScopeAndIntent(string $query, ?MemberDetail $member): string
    {
        $clean = strtolower($query);

        if ($member !== null) {
            if (str_contains($clean, 'direct') && str_contains($clean, 'income')) {
                return 'member_direct_income';
            }
            if (str_contains($clean, 'level') && str_contains($clean, 'income')) {
                return 'member_level_income';
            }
            if (str_contains($clean, 'income') || str_contains($clean, 'earning') || str_contains($clean, 'payout')) {
                return 'member_all_income';
            }
            if (str_contains($clean, 'wallet') || str_contains($clean, 'balance') || str_contains($clean, 'amount')) {
                return 'member_wallet';
            }
            if (str_contains($clean, 'withdrawal') || str_contains($clean, 'payout') || str_contains($clean, 'withdraw')) {
                return 'member_withdrawal';
            }
            if (str_contains($clean, 'ticket') || str_contains($clean, 'support')) {
                return 'member_tickets';
            }
            return 'member_profile';
        }

        // Global Intents
        if (str_contains($clean, 'member') || str_contains($clean, 'user') || str_contains($clean, 'sabhi') || str_contains($clean, 'sabka')) {
            if (str_contains($clean, 'wallet') || str_contains($clean, 'balance') || str_contains($clean, 'amount') || str_contains($clean, 'paisa')) {
                return 'global_wallets';
            }
            if (str_contains($clean, 'income') || str_contains($clean, 'earning') || str_contains($clean, 'revenue')) {
                return 'global_income';
            }
            return 'global_members';
        }

        if (str_contains($clean, 'withdrawal') || str_contains($clean, 'payout') || str_contains($clean, 'withdraw')) {
            return 'global_withdrawals';
        }
        if (str_contains($clean, 'income') || str_contains($clean, 'earning') || str_contains($clean, 'revenue')) {
            return 'global_income';
        }
        if (str_contains($clean, 'ticket') || str_contains($clean, 'support')) {
            return 'global_tickets';
        }
        if (str_contains($clean, 'fund') || str_contains($clean, 'deposit') || str_contains($clean, 'payment')) {
            return 'global_funds';
        }
        if (str_contains($clean, 'workflow') || str_contains($clean, 'package') || str_contains($clean, 'activation') || str_contains($clean, 'staking')) {
            return 'global_workflow';
        }
        if (str_contains($clean, 'system') || str_contains($clean, 'summary') || str_contains($clean, 'dashboard') || str_contains($clean, 'status')) {
            return 'global_dashboard';
        }

        return 'global_dashboard';
    }

    /**
     * Compile verified read-only database context for administrator.
     */
    public function compileContext(string $intent, ?MemberDetail $member): array
    {
        if ($member !== null) {
            $mCode = (string) $member->memberid;

            if ($intent === 'member_direct_income') {
                $direct = Schema::hasTable('direct_incomes')
                    ? (float) DirectIncome::where('memberid', $mCode)->sum('amount')
                    : 0;
                return [
                    'scope' => 'member_direct_income',
                    'member_id' => $mCode,
                    'name' => $member->name,
                    'direct_income' => $direct,
                ];
            }

            if ($intent === 'member_level_income') {
                $level = Schema::hasTable('level_incomes')
                    ? (float) LevelIncome::where('memberid', $mCode)->sum('amount')
                    : 0;
                return [
                    'scope' => 'member_level_income',
                    'member_id' => $mCode,
                    'name' => $member->name,
                    'level_income' => $level,
                ];
            }

            if ($intent === 'member_all_income') {
                $level = Schema::hasTable('level_incomes') ? (float) LevelIncome::where('memberid', $mCode)->sum('amount') : 0;
                $direct = Schema::hasTable('direct_incomes') ? (float) DirectIncome::where('memberid', $mCode)->sum('amount') : 0;
                $staking = Schema::hasTable('staking_incomes') ? (float) StakingIncome::where('memberid', $mCode)->sum('amount') : 0;
                $daily = Schema::hasTable('daily_incomes')
                    ? (float) DailyIncome::where(function ($q) use ($mCode, $member) {
                        $q->where('member_id', $mCode)->orWhere('member_id', (string) $member->id);
                    })->sum('amount')
                    : 0;
                $reward = Schema::hasTable('reward_incomes') ? (float) RewardIncome::where('memberid', $mCode)->sum('amount') : 0;

                return [
                    'scope' => 'member_all_income',
                    'member_id' => $mCode,
                    'name' => $member->name,
                    'level_income' => $level,
                    'direct_income' => $direct,
                    'staking_income' => $staking,
                    'daily_roi' => $daily,
                    'reward_income' => $reward,
                    'total_income' => ($level + $direct + $staking + $daily + $reward),
                ];
            }

            if ($intent === 'member_wallet') {
                return [
                    'scope' => 'member_wallet',
                    'member_id' => $mCode,
                    'name' => $member->name,
                    'cash_wallet' => (float) $member->wallet,
                    'p2p_wallet' => (float) $member->p2p_wallet,
                    'coin_wallet' => (float) ($member->coin_wallet ?? 0),
                    'total_wallet' => (float) ($member->wallet + $member->p2p_wallet),
                ];
            }

            if ($intent === 'member_withdrawal') {
                $approved = Schema::hasTable('withdrawal_requests')
                    ? (float) WithdrawalRequest::where('memberid', $mCode)->whereIn('status', ['Paid', 'Approved', 'Success'])->sum('gross_amount')
                    : 0;
                $pending = Schema::hasTable('withdrawal_requests')
                    ? (float) WithdrawalRequest::where('memberid', $mCode)->whereIn('status', ['Pending', 'Requested'])->sum('gross_amount')
                    : 0;

                return [
                    'scope' => 'member_withdrawal',
                    'member_id' => $mCode,
                    'name' => $member->name,
                    'approved_withdrawals' => $approved,
                    'pending_withdrawals' => $pending,
                ];
            }

            if ($intent === 'member_tickets') {
                $tickets = Schema::hasTable('support_tickets')
                    ? SupportTicket::where('memberid', $mCode)->get(['ticket_id', 'subject', 'status'])->toArray()
                    : [];

                return [
                    'scope' => 'member_tickets',
                    'member_id' => $mCode,
                    'name' => $member->name,
                    'tickets' => $tickets,
                ];
            }

            // Member Profile (default)
            return [
                'scope' => 'member_profile',
                'member_id' => $mCode,
                'name' => $member->name,
                'email' => $member->email,
                'mobile' => $member->mobile,
                'status' => $member->status,
                'rank' => $member->rank ?: 'N/A',
                'active_package' => (float) $member->package,
                'package_name' => $member->package_name ?: 'Standard',
                'cash_wallet' => (float) $member->wallet,
                'p2p_wallet' => (float) $member->p2p_wallet,
                'self_business' => (float) $member->self_biz,
                'team_business' => (float) $member->team_biz,
                'wallet_address' => $member->wallet_address ?: 'N/A',
            ];
        }

        // Global Context Compilation
        if ($intent === 'global_wallets') {
            $cash = (float) MemberDetail::sum('wallet');
            $p2p = (float) MemberDetail::sum('p2p_wallet');
            return [
                'scope' => 'global_wallets',
                'total_cash_wallet' => $cash,
                'total_p2p_wallet' => $p2p,
                'total_combined_wallet' => ($cash + $p2p),
            ];
        }

        if ($intent === 'global_members') {
            return [
                'scope' => 'global_members',
                'total_members' => MemberDetail::count(),
                'active_members' => MemberDetail::where('status', 'Active')->count(),
                'temp_members' => MemberDetail::where('status', 'Temp')->count(),
                'blocked_members' => MemberDetail::where('status', 'Blocked')->count(),
            ];
        }

        if ($intent === 'global_withdrawals') {
            $approved = Schema::hasTable('withdrawal_requests')
                ? (float) WithdrawalRequest::whereIn('status', ['Paid', 'Approved', 'Success'])->sum('gross_amount')
                : 0;
            $pending = Schema::hasTable('withdrawal_requests')
                ? (float) WithdrawalRequest::whereIn('status', ['Pending', 'Requested'])->sum('gross_amount')
                : 0;
            $cancelled = Schema::hasTable('withdrawal_requests')
                ? (float) WithdrawalRequest::where('status', 'Cancelled')->sum('gross_amount')
                : 0;

            return [
                'scope' => 'global_withdrawals',
                'approved_withdrawals' => $approved,
                'pending_withdrawals' => $pending,
                'cancelled_withdrawals' => $cancelled,
            ];
        }

        if ($intent === 'global_income') {
            $level = Schema::hasTable('level_incomes') ? (float) LevelIncome::sum('amount') : 0;
            $direct = Schema::hasTable('direct_incomes') ? (float) DirectIncome::sum('amount') : 0;
            $staking = Schema::hasTable('staking_incomes') ? (float) StakingIncome::sum('amount') : 0;
            $daily = Schema::hasTable('daily_incomes') ? (float) DailyIncome::sum('amount') : 0;
            $reward = Schema::hasTable('reward_incomes') ? (float) RewardIncome::sum('amount') : 0;

            return [
                'scope' => 'global_income',
                'level_income' => $level,
                'direct_income' => $direct,
                'staking_income' => $staking,
                'daily_roi' => $daily,
                'reward_income' => $reward,
                'total_income' => ($level + $direct + $staking + $daily + $reward),
            ];
        }

        if ($intent === 'global_tickets') {
            return [
                'scope' => 'global_tickets',
                'total_tickets' => Schema::hasTable('support_tickets') ? SupportTicket::count() : 0,
                'open_tickets' => Schema::hasTable('support_tickets') ? SupportTicket::where('status', 'Open')->count() : 0,
                'closed_tickets' => Schema::hasTable('support_tickets') ? SupportTicket::where('status', 'Closed')->count() : 0,
            ];
        }

        if ($intent === 'global_funds') {
            return [
                'scope' => 'global_funds',
                'total_fund_requests' => Schema::hasTable('fund_requests') ? FundRequest::count() : 0,
                'pending_fund_requests' => Schema::hasTable('fund_requests') ? FundRequest::where('status', 'Requested')->count() : 0,
                'accepted_fund_requests' => Schema::hasTable('fund_requests') ? FundRequest::where('status', 'Accepted')->count() : 0,
            ];
        }

        if ($intent === 'global_workflow') {
            return [
                'scope' => 'global_workflow',
                'system_name' => config('app.name', 'Demo'),
                'activation_flow' => '1. Registration with sponsor ID. 2. Importing USDT/funds into wallet. 3. Activating Staking packages to generate Daily ROI and Level/Direct MLM commissions.',
            ];
        }

        // Global Dashboard (default)
        $totalMembers = MemberDetail::count();
        $activeMembers = MemberDetail::where('status', 'Active')->count();
        $totalP2p = (float) MemberDetail::sum('p2p_wallet');
        $approvedWithdrawals = Schema::hasTable('withdrawal_requests') ? (float) WithdrawalRequest::whereIn('status', ['Paid', 'Approved', 'Success'])->sum('gross_amount') : 0;
        $totalStaking = Schema::hasTable('staking_details') ? (float) StakingDetail::sum('invest_amount') : 0;

        return [
            'scope' => 'global_dashboard',
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'total_p2p_funds' => $totalP2p,
            'total_approved_withdrawals' => $approvedWithdrawals,
            'total_staking_volume' => $totalStaking,
        ];
    }

    /**
     * Build deterministic source-style fallback responses for admin queries.
     */
    public function buildFallbackResponse(string $intent, array $context): string
    {
        switch ($intent) {
            case 'member_direct_income':
                return "### Member {$context['member_id']} Direct Sponsor Income\n\n" .
                    "- **Name**: {$context['name']}\n" .
                    '- **Direct Sponsor Income**: ₹' . number_format($context['direct_income'], 2) . '.';

            case 'member_level_income':
                return "### Member {$context['member_id']} Level Income\n\n" .
                    "- **Name**: {$context['name']}\n" .
                    '- **Level Income**: ₹' . number_format($context['level_income'], 2) . '.';

            case 'member_all_income':
                return "### Member {$context['member_id']} Earnings Report\n\n" .
                    "- **Name**: {$context['name']}\n" .
                    '- Direct Sponsor Income: ₹' . number_format($context['direct_income'], 2) . "\n" .
                    '- Level Income: ₹' . number_format($context['level_income'], 2) . "\n" .
                    '- Staking Cashback Income: ₹' . number_format($context['staking_income'], 2) . "\n" .
                    '- Daily Trading ROI: ₹' . number_format($context['daily_roi'], 2) . "\n" .
                    '- Reward Income: ₹' . number_format($context['reward_income'], 2) . "\n\n" .
                    '- **Total MLM Income**: **₹' . number_format($context['total_income'], 2) . '**.';

            case 'member_wallet':
                return "### Member {$context['member_id']} Wallet Report\n\n" .
                    "- **Name**: {$context['name']}\n" .
                    '- **Cash Balance**: ₹' . number_format($context['cash_wallet'], 2) . "\n" .
                    '- **P2P Balance**: ₹' . number_format($context['p2p_wallet'], 2) . "\n" .
                    '- **Total Wallet Funds**: ₹' . number_format($context['total_wallet'], 2) . '.';

            case 'member_withdrawal':
                return "### Member {$context['member_id']} Withdrawal Report\n\n" .
                    "- **Name**: {$context['name']}\n" .
                    '- **Approved Payouts**: ₹' . number_format($context['approved_withdrawals'], 2) . "\n" .
                    '- **Pending Payouts**: ₹' . number_format($context['pending_withdrawals'], 2) . '.';

            case 'member_tickets':
                $list = '';
                foreach ($context['tickets'] ?? [] as $ticket) {
                    $list .= "- Ticket ID: {$ticket['ticket_id']} | Subject: {$ticket['subject']} | Status: {$ticket['status']}\n";
                }
                return "### Member {$context['member_id']} Support Tickets\n\n" .
                    ($list ?: '_No support tickets created yet._');

            case 'member_profile':
                return "### Member {$context['member_id']} Account Summary\n\n" .
                    "- **Name**: {$context['name']}\n" .
                    "- **Email**: {$context['email']}\n" .
                    "- **Status**: {$context['status']}\n" .
                    "- **Rank**: {$context['rank']}\n" .
                    '- **Active Package**: $' . number_format($context['active_package'], 2) . " ({$context['package_name']})\n" .
                    '- **Cash Wallet**: ₹' . number_format($context['cash_wallet'], 2) . "\n" .
                    '- **P2P Wallet**: ₹' . number_format($context['p2p_wallet'], 2) . "\n" .
                    '- **Self Business Vol**: ₹' . number_format($context['self_business'], 2) . "\n" .
                    '- **Team Business Vol**: ₹' . number_format($context['team_business'], 2) . '.';

            case 'global_wallets':
                return "### Global Wallet Balances (All Members combined)\n\n" .
                    '- **Total Cash Wallets Sum**: ₹' . number_format($context['total_cash_wallet'], 2) . "\n" .
                    '- **Total P2P Wallets Sum**: ₹' . number_format($context['total_p2p_wallet'], 2) . "\n" .
                    '- **Total Combined Funds**: **₹' . number_format($context['total_combined_wallet'], 2) . '**.';

            case 'global_dashboard':
                return "### Global Dashboard Summary\n\n" .
                    "- **Total Registered Members**: {$context['total_members']} ({$context['active_members']} Active)\n" .
                    '- **Total P2P Wallet Funds**: ₹' . number_format($context['total_p2p_funds'], 2) . "\n" .
                    '- **Total Approved Payouts**: ₹' . number_format($context['total_approved_withdrawals'], 2) . "\n" .
                    '- **Total Staking Investment Volume**: $' . number_format($context['total_staking_volume'], 2) . '.';

            case 'global_members':
                return "### Global Membership Count\n\n" .
                    "- **Total Registered**: {$context['total_members']}\n" .
                    "- **Active Accounts**: {$context['active_members']}\n" .
                    "- **Temp Accounts**: {$context['temp_members']}\n" .
                    "- **Blocked Accounts**: {$context['blocked_members']}.";

            case 'global_withdrawals':
                return "### Global Withdrawals Summary\n\n" .
                    '- **Approved Payouts**: ₹' . number_format($context['approved_withdrawals'], 2) . "\n" .
                    '- **Pending Payouts**: ₹' . number_format($context['pending_withdrawals'], 2) . "\n" .
                    '- **Cancelled Payouts**: ₹' . number_format($context['cancelled_withdrawals'], 2) . '.';

            case 'global_income':
                return "### Global Income Distribution Report\n\n" .
                    '- **Level Income Distributed**: ₹' . number_format($context['level_income'], 2) . "\n" .
                    '- **Direct Sponsor Income**: ₹' . number_format($context['direct_income'], 2) . "\n" .
                    '- **Staking Cashback Distributed**: ₹' . number_format($context['staking_income'], 2) . "\n" .
                    '- **Daily Trading ROI Distributed**: ₹' . number_format($context['daily_roi'], 2) . "\n" .
                    '- **Reward Income Distributed**: ₹' . number_format($context['reward_income'], 2) . "\n\n" .
                    '- **Total Distributed Payouts**: **₹' . number_format($context['total_income'], 2) . '**.';

            case 'global_tickets':
                return "### Global Support Tickets\n\n" .
                    "- **Total Support Tickets**: {$context['total_tickets']}\n" .
                    "- **Open Tickets**: {$context['open_tickets']}\n" .
                    "- **Closed Tickets**: {$context['closed_tickets']}.";

            case 'global_funds':
                return "### Global Funds Request Reports\n\n" .
                    "- **Total Requests**: {$context['total_fund_requests']}\n" .
                    "- **Pending Requests**: {$context['pending_fund_requests']}\n" .
                    "- **Approved Requests**: {$context['accepted_fund_requests']}.";

            case 'global_workflow':
                return "### System Staking & Activation Workflow\n\n" .
                    "1. **Registration**: Members sign up using a sponsor link.\n" .
                    "2. **Funds**: Members import funds (USDT/Instant) into their wallet balance.\n" .
                    "3. **Activation**: Members activate Staking packages using their wallet.\n" .
                    "4. **Incomes**: Daily ROI, Direct referral commissions, and Level bonuses are automatically credited.";

            default:
                return 'Main sirf Admin dashboard se related statistics (members, wallet balances, withdrawals, incomes, staking) aur codebase workflows ki jankari de sakta hoon.';
        }
    }
}
