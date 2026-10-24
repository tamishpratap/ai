<?php

namespace App\Http\Controllers;

// We import standard Laravel classes needed for the controller
use App\Models\AiConversation;
use App\Models\AiUsageLog;
use App\Models\DirectIncome;
use App\Models\LevelIncome;
use App\Models\MemberActivation;
use App\Models\MemberDetail;
use App\Models\OrderDetail;
use App\Models\RewardIncome;
use App\Models\StakingIncome;
use App\Models\SupportTicket;
use App\Models\WalletTransfer;
use App\Models\WithdrawalRequest;
use App\Services\Gemini\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MemberAiController extends Controller
{
    /**
     * General-purpose chat used only by the classic Demo Project AI workspace.
     */
    public function generalChat(Request $request, GeminiService $gemini)
    {
        $member = MemberDetail::find(session('MEMBER_ID'));
        $userName = $member?->name ?? session('MEMBER_NAME', 'Member');

        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'required|in:user,model',
            'history.*.parts' => 'required|array|max:1',
            'history.*.parts.0.text' => 'required|string|max:4000',
        ]);

        $contents = collect($validated['history'] ?? [])
            ->map(fn (array $item) => [
                'role' => $item['role'],
                'parts' => [['text' => $item['parts'][0]['text']]],
            ])
            ->values()
            ->all();
        $contents[] = ['role' => 'user', 'parts' => [['text' => $validated['message']]]];

        try {
            $result = $gemini->generate($contents);

            return response()->json([
                'success' => true,
                'answer' => $result['text'],
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Demo Project AI request fallback triggered', ['message' => $exception->getMessage()]);

            $fallback = $this->generateProjectAiFallback($validated['message'], $userName);

            return response()->json([
                'success' => true,
                'answer' => $fallback,
                'fallback' => true,
            ]);
        }
    }

    /**
     * Show the member chat page.
     *
     * This method renders the front-end chat page. It first checks if the member
     * is logged in by looking up the 'MEMBER_ID' session variable.
     */
    public function index()
    {
        $member = MemberDetail::find(session('MEMBER_ID'));

        return view('ai.chat', [
            'data' => $member ?? (object) ['name' => 'Guest', 'profile_image' => null],
        ]);
    }

    /**
     * Show the voice-enabled member assistant page.
     */
    public function voiceAssistant()
    {
        $member = MemberDetail::find(session('MEMBER_ID'));
        $memberId = $member?->id;

        $usage = $memberId ? AiUsageLog::where('member_id', $memberId) : null;
        $usageCount = $usage ? (clone $usage)->count() : 0;

        return view('frontend.ai.voice-assistant', [
            'data' => $member ?? (object) [
                'name' => session('MEMBER_NAME', 'Deepanshu Saini'),
                'email' => session('MEMBER_EMAIL', 'deepanshu@ai.com'),
                'mobile' => session('MEMBER_MOBILE', '9876543210'),
                'profile_image' => null,
            ],
            'assistantStats' => [
                'chats' => $memberId ? AiConversation::where('member_id', $memberId)->count() : 0,
                'tasks' => $usage ? (clone $usage)->whereIn('request_type', ['quick_action', 'pdf_summary'])->count() : 0,
                'usage_minutes' => $usage ? round(((clone $usage)->sum('duration_ms') ?: 0) / 60000, 1) : 0,
                'success_rate' => $usageCount ? round(((clone $usage)->where('status', 'success')->count() / $usageCount) * 100, 1) : 0,
            ],
        ]);
    }

    /**
     * Show the member-scoped project data voice assistant.
     */
    public function projectVoiceAssistant()
    {
        $memberId = session('MEMBER_ID');
        $member = MemberDetail::where('memberid', $memberId)->orWhere('id', $memberId)->first();

        if (! $member) {
            return redirect('/member');
        }

        $snapshot = $this->getCompleteMemberSnapshot($member);

        return view('member.ai.project-voice-assistant', [
            'data' => $member,
            'projectStats' => [
                'income_today' => $snapshot['incomes']['today_total'],
                'total_income' => $snapshot['incomes']['lifetime_total'],
                'business' => $snapshot['team']['team_business'],
                'self_biz' => $snapshot['team']['self_business'],
                'team_members' => $snapshot['team']['total_members'],
                'direct_team' => $snapshot['team']['direct_total'],
                'active_direct' => $snapshot['team']['direct_active'],
                'left_count' => $snapshot['team']['left_count'],
                'right_count' => $snapshot['team']['right_count'],
                'wallet_balance' => $snapshot['wallets']['total_balance'],
                'cash_wallet' => $snapshot['wallets']['cash_wallet'],
                'p2p_wallet' => $snapshot['wallets']['p2p_wallet'],
                'package' => $snapshot['capping']['active_package'],
                'rank' => $snapshot['profile']['rank'],
                'status' => $snapshot['profile']['status'],
                'total_capping' => $snapshot['capping']['total_capping'],
                'achieved_capping' => $snapshot['capping']['achieved_capping'],
                'remaining_capping' => $snapshot['capping']['remaining_capping'],
                'total_withdrawals' => $snapshot['withdrawals']['total_approved'],
            ],
        ]);
    }

    /**
     * Handle the chat query from the member.
     *
     * This method receives the text question via an AJAX POST request, parses the query,
     * pulls appropriate numbers from the database, and uses Google Gemini to answer it.
     */
    public function projectVoiceChat(Request $request)
    {
        // Step A: Validate that the client sent a valid message
        $request->validate([
            'message' => 'required|string|max:1000',
            'spoken_message' => 'nullable|string|max:1000',
        ]);

        // Step B: Fetch the logged-in member's ID from the session for security
        $id = session('MEMBER_ID');
        $member = MemberDetail::where('memberid', $id)->orWhere('id', $id)->first();

        // If the session has expired, return a 401 Unauthenticated JSON response
        if (! $member) {
            return response()->json([
                'success' => false,
                'answer' => 'Session expired. Please log in again.',
                'spoken_text' => 'Session expired. Please log in again.',
            ], 401);
        }

        $memberCode = (string) $member->memberid;

        // Retrieve the clean query from the input message
        $query = trim((string) $request->post('message'));
        $spokenQuery = trim((string) $request->post('spoken_message', ''));
        $lowercaseQuery = $this->normalizeProjectQuestion($query.' '.$spokenQuery);

        // A project assistant request is always bound to the active session member.
        // If another member code is explicitly requested, reject it instead of silently
        // resolving or leaking any cross-member information.
        preg_match_all('/\b(QRB|MLM|[A-Z]{2,4})\d{4,}\b/i', $query.' '.$spokenQuery, $requestedMemberIds);
        foreach ($requestedMemberIds[0] ?? [] as $requestedMemberId) {
            $normalizeId = fn (string $value) => preg_replace('/[^A-Z0-9]/', '', strtoupper($value));
            if ($normalizeId($requestedMemberId) !== $normalizeId($memberCode)) {
                return response()->json([
                    'success' => true,
                    'answer' => 'Security aur privacy ke liye main kisi doosri Member ID ka data nahi bata sakta. Main sirf aapki logged-in ID '.$memberCode.' ki verified details bataunga.',
                    'spoken_text' => 'Security aur privacy ke liye main kisi doosri Member ID ka data nahi bata sakta. Main sirf aapki logged-in ID '.$memberCode.' ki verified details bataunga.',
                ]);
            }
        }

        // Step C: Basic security check to block typical database manipulation attempts
        $blockedKeywords = ['database dump', 'drop table', 'union select', 'passwords', 'secret key'];
        foreach ($blockedKeywords as $word) {
            if (str_contains($lowercaseQuery, $word)) {
                return response()->json([
                    'success' => true,
                    'answer' => 'Main sirf aapki logged-in ID ke verified data ki jankari de sakta hoon. Aap income, wallet, transactions, business, team, profile, activation, withdrawals, orders ya support tickets ke baare mein poochh sakte hain.',
                ]);
            }
        }

        // Step D: Parse dates if specified (e.g. today, yesterday, or explicit dates like 02-06-2026)
        $targetDate = null;
        $startDate = null;
        $endDate = null;

        // Check for general relative dates
        if (str_contains($lowercaseQuery, 'today') || str_contains($lowercaseQuery, 'aaj') || str_contains($lowercaseQuery, 'आज')) {
            $targetDate = date('Y-m-d');
        } elseif (str_contains($lowercaseQuery, 'yesterday') || str_contains($lowercaseQuery, 'kal') || str_contains($lowercaseQuery, 'कल')) {
            $targetDate = date('Y-m-d', strtotime('yesterday'));
        }

        // Extract dates formatted as DD-MM-YYYY or DD/MM/YYYY using simple Regular Expressions
        preg_match_all('~(\d{1,2})[-/](\d{1,2})[-/](\d{4})~', $query, $matches);
        if (! empty($matches[0])) {
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

        // Step E: Understand approximate Hindi, Hinglish and English phrasing.
        $intent = $this->detectProjectIntent($lowercaseQuery);

        if ($intent === 'unknown') {
            $intent = 'overview';
        }

        // Step F: Build a complete, member-scoped snapshot from the database
        $snapshot = $this->getCompleteMemberSnapshot($member, $targetDate, $startDate, $endDate);

        // Step G: Send request to external Google Gemini API
        try {
            $apiKey = config('gemini.api_key');

            $systemInstruction = 'You are Quantum Voice AI (NEXUS), speaking with the signature smooth, warm, and natural human-like voice persona "Kore" for the logged-in member '.$memberCode.' in the Quantum project member panel. '
                .'Currency is USD ($). The detected user intent is "'.$intent.'". '
                .'You must answer ONLY using the verified facts provided in VERIFIED_MEMBER_DATA below. '
                .'CRITICAL INSTRUCTIONS: '
                .'1. You represent ONLY this logged-in member. Never discuss or reveal another member\'s information or platform administrator secrets. '
                .'2. You know everything about this logged-in member: Name, Member ID, Rank, Status, KYC, Registration Date, Activation Date, Sponsor details, Referral Invite Link, Wallets (Cash, P2P/Deposit, total balance, deposits), Incomes (today, lifetime, direct, level, staking, reward), Package & 3X Capping limit, Team & Downlines (left/right, directs, power leg, other leg), Withdrawals, Transactions, Bank details (masked account number only), Orders and Support tickets. '
                .'3. If asked for their referral link, provide '.$snapshot['profile']['referral_link'].'. '
                .'4. If asked for full summary or "sab kuch" or "overall details", provide a clean, comprehensive overview of their account. '
                .'5. Never reveal unmasked passwords or secret keys. '
                .'6. Reply in smooth, warm, natural conversational Hindi/Hinglish (or English if the user asked in English). Keep sentences concise, melodic, and pleasant for voice synthesis, avoiding robotic phrasing or markdown tables. '
                .'VERIFIED_MEMBER_DATA='.json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $response = Http::withHeaders(['Content-Type' => 'application/json', 'x-goog-api-key' => $apiKey])
                ->timeout(5)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/'.config('gemini.default_model').':generateContent', [
                    'systemInstruction' => ['parts' => [['text' => $systemInstruction]]],
                    'contents' => [
                        ['role' => 'user', 'parts' => [['text' => $spokenQuery !== '' ? $query."\nOriginal speech: ".$spokenQuery : $query]]],
                    ],
                    'generationConfig' => ['temperature' => 0.25, 'maxOutputTokens' => 350],
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $answer = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($answer) {
                    $cleanedAnswer = trim($answer);
                    $spoken = trim(preg_replace('/[*_#`\[\]]/', '', strip_tags($cleanedAnswer)));
                    $spoken = preg_replace('/\s+/', ' ', $spoken);

                    return response()->json([
                        'success' => true,
                        'answer' => $cleanedAnswer,
                        'spoken_text' => $spoken,
                    ]);
                }
            }
            throw new \Exception('External API Error');
        } catch (\Exception $e) {
            Log::warning('Member AI API failure: '.$e->getMessage().'. Using fallback template.');

            // Step H: Fallback local Hinglish templated response generator
            $answer = $this->buildFallbackResponse($intent, $snapshot);
            $spoken = trim(preg_replace('/[*_#`\[\]]/', '', strip_tags($answer)));
            $spoken = preg_replace('/\s+/', ' ', $spoken);

            return response()->json([
                'success' => true,
                'answer' => $answer,
                'spoken_text' => $spoken,
            ]);
        }
    }

    /**
     * Backwards-compatible handler for the older text AI route.
     */
    public function chat(Request $request)
    {
        return $this->projectVoiceChat($request);
    }

    private function normalizeProjectQuestion(string $query): string
    {
        $query = mb_strtolower($query, 'UTF-8');
        $query = str_replace(['’', "'", '-', '_'], ' ', $query);

        return trim((string) preg_replace('/[^\p{L}\p{N}\s\/]+/u', ' ', preg_replace('/\s+/u', ' ', $query)));
    }

    private function detectProjectIntent(string $query): string
    {
        $keywords = [
            'overview' => [
                'summary', 'overview', 'sab kuch', 'poora data', 'pura data', 'complete detail', 'sari detail',
                'saari detail', 'saari details', 'sari jankari', 'overall detail', 'overall details', 'meri detail',
                'meri details', 'mera account', 'sab kuch batao', 'full detail', 'full details', 'all details',
                'sara data', 'meri jankari', 'सारांश', 'पूरी जानकारी', 'सब कुछ', 'सारी डिटेल', 'अकाउंट समरी'
            ],
            'referral' => [
                'referral link', 'reffer link', 'refer link', 'invite link', 'share link', 'sponsor link',
                'joining link', 'link batao', 'link do', 'mera link', 'refferel link', 'रेफरल लिंक', 'रेफर लिंक', 'इनवाइट लिंक'
            ],
            'sponsor' => [
                'sponsor', 'upline', 'sponsor id', 'sponsor name', 'sponsor kaun', 'upline id', 'upline name',
                'kisne joda', 'स्पॉन्सर', 'अपलाइन'
            ],
            'rank' => [
                'rank', 'my rank', 'mera rank', 'level rank', 'achievement', 'रैंक', 'पद'
            ],
            'bank' => [
                'bank', 'account number', 'ifsc', 'bank detail', 'bank details', 'khata number', 'बैंक', 'खाता'
            ],
            'capping' => [
                'capping', '3x', 'limit', 'capping limit', 'package limit', '3x capping', 'कैपिंग', 'लिमिट'
            ],
            'withdrawal' => [
                'withdrawal', 'withdraw', 'payout', 'nikasi', 'nikalna', 'cash out', 'withdrawn', 'विड्रॉल', 'निकासी', 'पैसे निकाले'
            ],
            'orders' => [
                'order', 'product', 'purchase', 'booking', 'kharida', 'ऑर्डर', 'प्रोडक्ट', 'खरीद'
            ],
            'tickets' => [
                'ticket', 'support', 'complaint', 'help request', 'शिकायत', 'सपोर्ट', 'टिकट'
            ],
            'transactions' => [
                'transaction', 'wallet history', 'statement', 'ledger', 'len den', 'entry', 'ट्रांजैक्शन', 'लेन देन', 'स्टेटमेंट'
            ],
            'activation' => [
                'activation', 'activate', 'joining', 'active kab', 'joining date', 'activation date', 'कब जुड़ा', 'आईडी एक्टिव', 'एक्टिवेशन', 'जॉइनिंग'
            ],
            'wallet' => [
                'wallet', 'balance', 'paisa', 'paise', 'available amount', 'cash wallet', 'p2p wallet', 'deposit balance', 'वॉलेट', 'बैलेंस', 'पैसा', 'राशि'
            ],
            'income' => [
                'income', 'earning', 'kamai', 'bonus', 'commission', 'profit', 'aamdani', 'incomes', 'daily income', 'इनकम', 'कमाई', 'आमदनी', 'बोनस'
            ],
            'team' => [
                'downline', 'team', 'network', 'referral', 'direct member', 'left right', 'members', 'directs', 'direct referrals', 'active team', 'टीम', 'डाउनलाइन', 'रेफरल', 'सदस्य'
            ],
            'investment' => [
                'investment', 'package', 'plan', 'business', 'biz', 'self business', 'team business', 'power leg', 'other leg', 'निवेश', 'पैकेज', 'बिजनेस', 'व्यापार'
            ],
            'profile' => [
                'profile', 'account', 'status', 'kyc', 'member id', 'meri id', 'mera naam', 'email', 'mobile', 'phone', 'country', 'details', 'प्रोफाइल', 'खाता', 'मेरी आईडी', 'मेरा नाम', 'मोबाइल'
            ],
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
                    if (! preg_match('/^[a-z0-9]+$/', $token) || ! preg_match('/^[a-z0-9]+$/', $phrase)) {
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

        return $bestScore >= 2 ? $bestIntent : 'unknown';
    }

    /**
     * Build complete snapshot of all member data.
     */
    private function getCompleteMemberSnapshot(MemberDetail $member, ?string $targetDate = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $memberCode = (string) $member->memberid;
        $today = now()->toDateString();

        $incomesToday = $this->getMemberIncomes($memberCode, $today);
        $lifetimeIncomes = $this->getMemberIncomes($memberCode);
        $customIncomes = ($targetDate || ($startDate && $endDate))
            ? $this->getMemberIncomes($memberCode, $targetDate, $startDate, $endDate)
            : null;

        try {
            $totalCapping = function_exists('capping') ? (float) capping($memberCode) : ((float) $member->package * 3);
        } catch (\Throwable $e) {
            $totalCapping = (float) $member->package * 3;
        }

        try {
            $achievedCapping = function_exists('Income3xachieved') ? (float) Income3xachieved($memberCode) : $lifetimeIncomes['total'];
        } catch (\Throwable $e) {
            $achievedCapping = $lifetimeIncomes['total'];
        }
        $remainingCapping = max(0.0, $totalCapping - $achievedCapping);
        $cappingPct = ($totalCapping > 0) ? round(($achievedCapping / $totalCapping) * 100, 1) : 0.0;

        $directTotal = (int) ($member->downline ?: MemberDetail::where('sponsorid', $memberCode)->count());
        try {
            $directActive = function_exists('activeReferralMember')
                ? (int) activeReferralMember($memberCode)
                : MemberDetail::where([['sponsorid', $memberCode], ['status', 'Active']])->count();
        } catch (\Throwable $e) {
            $directActive = MemberDetail::where([['sponsorid', $memberCode], ['status', 'Active']])->count();
        }

        $totalTeam = (int) $member->left_count + (int) $member->right_count + $directTotal;
        try {
            if (function_exists('totalLevelMembers')) {
                $lvlMembers = (int) totalLevelMembers($memberCode);
                if ($lvlMembers > 0) {
                    $totalTeam = $lvlMembers;
                }
            }
        } catch (\Throwable $e) {
        }

        $activeTeam = 0;
        try {
            if (function_exists('activeTeamMember')) {
                $activeTeam = (int) activeTeamMember($memberCode);
            }
        } catch (\Throwable $e) {
        }

        $sponsorName = ($member->sponsorid && strtolower((string) $member->sponsorid) !== 'root')
            ? (MemberDetail::where('memberid', $member->sponsorid)->value('name') ?? 'Root')
            : 'Root';

        $totalWithdrawalsVal = 0.0;
        try {
            $totalWithdrawalsVal = function_exists('totalWithdrawals')
                ? (float) totalWithdrawals($memberCode)
                : (Schema::hasTable('withdrawal_requests') ? (float) WithdrawalRequest::where([['memberid', $memberCode], ['status', 'Approved']])->sum('gross_amount') : 0.0);
        } catch (\Throwable $e) {
            $totalWithdrawalsVal = Schema::hasTable('withdrawal_requests') ? (float) WithdrawalRequest::where([['memberid', $memberCode], ['status', 'Approved']])->sum('gross_amount') : 0.0;
        }

        $todayWithdrawalsVal = 0.0;
        try {
            $todayWithdrawalsVal = function_exists('todayWithdrawals')
                ? (float) todayWithdrawals($memberCode)
                : (Schema::hasTable('withdrawal_requests') ? (float) WithdrawalRequest::where([['memberid', $memberCode], ['status', 'Approved']])->whereDate('created_at', $today)->sum('gross_amount') : 0.0);
        } catch (\Throwable $e) {
            $todayWithdrawalsVal = Schema::hasTable('withdrawal_requests') ? (float) WithdrawalRequest::where([['memberid', $memberCode], ['status', 'Approved']])->whereDate('created_at', $today)->sum('gross_amount') : 0.0;
        }

        $totalDepositVal = 0.0;
        try {
            $totalDepositVal = function_exists('totalDeposit') ? (float) totalDeposit($memberCode) : 0.0;
        } catch (\Throwable $e) {
        }

        $powerLegVal = 0.0;
        $otherLegVal = 0.0;
        try {
            $powerLegVal = function_exists('powerLegBiz') ? (float) powerLegBiz($memberCode) : 0.0;
            $otherLegVal = function_exists('otherLegBiz') ? (float) otherLegBiz($memberCode) : 0.0;
        } catch (\Throwable $e) {
        }

        $referralUrl = config('detailsApp.url')
            ? rtrim((string) config('detailsApp.url'), '/').'/member/register/'.$memberCode
            : url('/member/register/'.$memberCode);

        $recentWithdrawals = Schema::hasTable('withdrawal_requests')
            ? WithdrawalRequest::where('memberid', $memberCode)->latest()->limit(5)->get(['request_id', 'gross_amount', 'net_amount', 'status', 'created_at'])->toArray()
            : [];

        $recentTransfers = Schema::hasTable('wallet_transfers')
            ? WalletTransfer::where('memberid', $memberCode)->latest()->limit(5)->get(['walletType', 'debit', 'credit', 'balance', 'particular', 'created_at'])->toArray()
            : [];

        $orderCount = Schema::hasTable('order_details')
            ? OrderDetail::where('memberid', $memberCode)->where('status', '!=', 'Temp')->count()
            : 0;

        $ticketCount = Schema::hasTable('support_tickets')
            ? SupportTicket::where('memberid', $memberCode)->count()
            : 0;

        return [
            'profile' => [
                'member_id' => $memberCode,
                'name' => $member->name,
                'email' => $member->email,
                'mobile' => $member->mobile,
                'rank' => $member->rank ?: 'Member',
                'status' => $member->status,
                'kyc_status' => $member->kyc_status ?: 'Not Submitted',
                'country' => $member->country ?: 'N/A',
                'sponsor_id' => $member->sponsorid ?: 'Root',
                'sponsor_name' => $sponsorName,
                'placement_id' => $member->placementid ?: 'N/A',
                'position' => $member->position ?: 'N/A',
                'registration_date' => $member->created_at ? $member->created_at->format('d M Y') : 'N/A',
                'activation_date' => $member->activated_at ?: 'Not Activated Yet',
                'referral_link' => $referralUrl,
                'bank_name' => $member->bank ?: 'N/A',
                'bank_holder' => $member->holder ?: 'N/A',
                'bank_account' => filled($member->account_no) ? 'XXXX'.substr((string) $member->account_no, -4) : 'N/A',
                'ifsc_code' => $member->ifsc ?: 'N/A',
            ],
            'wallets' => [
                'currency' => 'USD ($)',
                'cash_wallet' => (float) $member->wallet,
                'p2p_wallet' => (float) $member->p2p_wallet,
                'total_balance' => (float) ($member->wallet + $member->p2p_wallet),
                'total_deposit' => $totalDepositVal,
            ],
            'incomes' => [
                'today_total' => $incomesToday['total'],
                'today_breakdown' => [
                    'direct' => $incomesToday['direct'],
                    'level' => $incomesToday['level'],
                    'staking' => $incomesToday['staking'],
                    'reward' => $incomesToday['reward'],
                ],
                'lifetime_total' => function_exists('totalIncome') ? (float) totalIncome($memberCode) : $lifetimeIncomes['total'],
                'lifetime_breakdown' => [
                    'direct' => function_exists('totalDirectIncome') ? (float) totalDirectIncome($memberCode) : $lifetimeIncomes['direct'],
                    'level' => function_exists('totalLevelIncome') ? (float) totalLevelIncome($memberCode) : $lifetimeIncomes['level'],
                    'staking' => function_exists('totalStakingIncome') ? (float) totalStakingIncome($memberCode) : $lifetimeIncomes['staking'],
                    'reward' => function_exists('totalRewardIncome') ? (float) totalRewardIncome($memberCode) : $lifetimeIncomes['reward'],
                ],
                'custom_filter' => $customIncomes ? [
                    'range' => ($targetDate ?: ($startDate.' to '.$endDate)),
                    'total' => $customIncomes['total'],
                    'breakdown' => $customIncomes,
                ] : null,
            ],
            'capping' => [
                'active_package' => (float) $member->package,
                'total_capping' => $totalCapping,
                'achieved_capping' => $achievedCapping,
                'remaining_capping' => $remainingCapping,
                'percentage' => $cappingPct,
            ],
            'team' => [
                'self_business' => (float) $member->self_biz,
                'team_business' => (float) $member->team_biz,
                'power_leg_business' => $powerLegVal,
                'other_leg_business' => $otherLegVal,
                'direct_total' => $directTotal,
                'direct_active' => $directActive,
                'total_members' => $totalTeam,
                'active_members' => $activeTeam,
                'left_count' => (int) $member->left_count,
                'right_count' => (int) $member->right_count,
            ],
            'withdrawals' => [
                'today_withdrawals' => $todayWithdrawalsVal,
                'total_approved' => $totalWithdrawalsVal,
                'recent' => $recentWithdrawals,
            ],
            'transactions' => [
                'recent' => $recentTransfers,
            ],
            'orders' => [
                'total_orders' => $orderCount,
            ],
            'support' => [
                'total_tickets' => $ticketCount,
            ],
        ];
    }

    /**
     * Generate localized Hinglish responses when external API is unreachable.
     */
    private function buildFallbackResponse(string $intent, array $snapshot): string
    {
        switch ($intent) {
            case 'overview':
                return "Aapke account ({$snapshot['profile']['member_id']}) ka complete summary:\n".
                       "- Name / Rank: **{$snapshot['profile']['name']}** (Rank: **{$snapshot['profile']['rank']}**, Status: **{$snapshot['profile']['status']}**)\n".
                       "- Active Package: **$".number_format($snapshot['capping']['active_package'], 2)."** (3X Capping: **$".number_format($snapshot['capping']['total_capping'], 2)."**, Remaining: **$".number_format($snapshot['capping']['remaining_capping'], 2)."**)\n".
                       "- Cash Wallet: **$".number_format($snapshot['wallets']['cash_wallet'], 2)."**, P2P Wallet: **$".number_format($snapshot['wallets']['p2p_wallet'], 2)."** (Total: **$".number_format($snapshot['wallets']['total_balance'], 2)."**)\n".
                       "- Aaj ki Income: **$".number_format($snapshot['incomes']['today_total'], 2)."**, Lifetime Total Income: **$".number_format($snapshot['incomes']['lifetime_total'], 2)."**\n".
                       "- Team: **{$snapshot['team']['total_members']}** members (Active: **{$snapshot['team']['active_members']}**), Direct Referrals: **{$snapshot['team']['direct_total']}**\n".
                       "- Self Business: **$".number_format($snapshot['team']['self_business'], 2)."**, Team Business: **$".number_format($snapshot['team']['team_business'], 2)."**.";

            case 'referral':
                return "Aapka personal referral invite link yeh hai:\n**{$snapshot['profile']['referral_link']}**\n\nAapka Sponsor ID **{$snapshot['profile']['sponsor_id']}** ({$snapshot['profile']['sponsor_name']}) hai.";

            case 'rank':
                return "Aapka current platform rank **{$snapshot['profile']['rank']}** hai aur account status **{$snapshot['profile']['status']}** (KYC: {$snapshot['profile']['kyc_status']}) hai.";

            case 'sponsor':
                return "Aapke sponsor ki ID **{$snapshot['profile']['sponsor_id']}** aur unka naam **{$snapshot['profile']['sponsor_name']}** hai.";

            case 'bank':
                return "Aapki verified bank details:\n".
                       "- Bank Name: **{$snapshot['profile']['bank_name']}**\n".
                       "- Account Holder: **{$snapshot['profile']['bank_holder']}**\n".
                       "- Account Number: **{$snapshot['profile']['bank_account']}**\n".
                       "- IFSC Code: **{$snapshot['profile']['ifsc_code']}**.";

            case 'wallet':
                return "Aapka Cash Wallet Balance **$".number_format($snapshot['wallets']['cash_wallet'], 2)."** aur P2P/Deposit Wallet **$".number_format($snapshot['wallets']['p2p_wallet'], 2)."** hai. Total available balance **$".number_format($snapshot['wallets']['total_balance'], 2)."** hai (Total Deposit: $".number_format($snapshot['wallets']['total_deposit'], 2).").";

            case 'capping':
                return "Aapka active package **$".number_format($snapshot['capping']['active_package'], 2)."** hai. 3X Capping limit **$".number_format($snapshot['capping']['total_capping'], 2)."** me se aapne **$".number_format($snapshot['capping']['achieved_capping'], 2)."** ({$snapshot['capping']['percentage']}%) achieve kar liya hai. Remaining capping limit **$".number_format($snapshot['capping']['remaining_capping'], 2)."** bachi hai.";

            case 'income':
                if (! empty($snapshot['incomes']['custom_filter'])) {
                    $f = $snapshot['incomes']['custom_filter'];

                    return "Aapko select ki gayi date range ({$f['range']}) me total **$".number_format($f['total'], 2)."** income mili hai (Direct: $".number_format($f['breakdown']['direct'], 2).", Level: $".number_format($f['breakdown']['level'], 2).", Staking: $".number_format($f['breakdown']['staking'], 2).", Reward: $".number_format($f['breakdown']['reward'], 2).").";
                }

                return "Aapki aaj ki total income **$".number_format($snapshot['incomes']['today_total'], 2)."** hai aur lifetime total income **$".number_format($snapshot['incomes']['lifetime_total'], 2)."** hai.\n\nLifetime Breakdown:\n".
                       "- Direct Sponsor Income: $".number_format($snapshot['incomes']['lifetime_breakdown']['direct'], 2)."\n".
                       "- Level Income: $".number_format($snapshot['incomes']['lifetime_breakdown']['level'], 2)."\n".
                       "- Staking Income: $".number_format($snapshot['incomes']['lifetime_breakdown']['staking'], 2)."\n".
                       "- Reward Income: $".number_format($snapshot['incomes']['lifetime_breakdown']['reward'], 2).".";

            case 'team':
                return "Aapki team details:\n".
                       "- Total Team Members: **{$snapshot['team']['total_members']}** (Active: **{$snapshot['team']['active_members']}**)\n".
                       "- Direct Referrals: **{$snapshot['team']['direct_total']}** (Active: **{$snapshot['team']['direct_active']}**)\n".
                       "- Left Team: **{$snapshot['team']['left_count']}**, Right Team: **{$snapshot['team']['right_count']}**.";

            case 'investment':
                return "Aapka active package **$".number_format($snapshot['capping']['active_package'], 2)."** hai. Self Business **$".number_format($snapshot['team']['self_business'], 2)."** aur Total Team Business **$".number_format($snapshot['team']['team_business'], 2)."** hai (Power Leg: $".number_format($snapshot['team']['power_leg_business'], 2).", Other Leg: $".number_format($snapshot['team']['other_leg_business'], 2).").";

            case 'withdrawal':
                return "Aapka total approved withdrawal **$".number_format($snapshot['withdrawals']['total_approved'], 2)."** hai aur aaj ka withdrawal **$".number_format($snapshot['withdrawals']['today_withdrawals'], 2)."** hai. Total recent records: **".count($snapshot['withdrawals']['recent'])."**.";

            case 'transactions':
                return "Aapke account me Cash Wallet **$".number_format($snapshot['wallets']['cash_wallet'], 2)."**, P2P Wallet **$".number_format($snapshot['wallets']['p2p_wallet'], 2)."** aur recent wallet transactions **".count($snapshot['transactions']['recent'])."** hain.";

            case 'orders':
                return "Aapki logged-in ID ke recent confirmed orders **{$snapshot['orders']['total_orders']}** hain.";

            case 'tickets':
                return "Aapki logged-in ID ke total support tickets **{$snapshot['support']['total_tickets']}** hain.";

            case 'activation':
                return "Aapka account status **{$snapshot['profile']['status']}**, active package **$".number_format($snapshot['capping']['active_package'], 2)."**, joining date **{$snapshot['profile']['registration_date']}** aur activation date **{$snapshot['profile']['activation_date']}** hai.";

            case 'profile':
                return "Aapki profile details:\n".
                       "- Name: **{$snapshot['profile']['name']}**\n".
                       "- Member ID: **{$snapshot['profile']['member_id']}**\n".
                       "- Email: **{$snapshot['profile']['email']}**\n".
                       "- Mobile: **{$snapshot['profile']['mobile']}**\n".
                       "- Rank / Status: **{$snapshot['profile']['rank']}** / **{$snapshot['profile']['status']}**\n".
                       "- KYC Status: **{$snapshot['profile']['kyc_status']}**\n".
                       "- Sponsor: **{$snapshot['profile']['sponsor_id']}** ({$snapshot['profile']['sponsor_name']})\n".
                       "- Country: **{$snapshot['profile']['country']}**\n".
                       "- Joining Date: **{$snapshot['profile']['registration_date']}**\n".
                       "- Activation Date: **{$snapshot['profile']['activation_date']}**.";

            default:
                return 'Main sirf aapke account ke data ki jankari de sakta hoon. Kripya apne wallet, income, referral link, downline, capping ya profile se related sawal poochhein.';
        }
    }

    public function projectAI(Request $request)
    {
        $member = MemberDetail::find(session('MEMBER_ID'));

        $memberData = $member ?? (object) [
            'name' => session('MEMBER_NAME', 'Deepanshu Saini'),
            'email' => session('MEMBER_EMAIL', 'deepanshu@ai.com'),
            'mobile' => session('MEMBER_MOBILE', '9876543210'),
            'profile_image' => null,
        ];

        return view('frontend.ai.project-ai', ['data' => $memberData]);
    }

    /**
     * Contextual intelligent fallback for Project AI workspace.
     */
    private function generateProjectAiFallback(string $message, string $userName): string
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'launch plan') || str_contains($lower, 'roadmap') || str_contains($lower, 'launch')) {
            return "### 🚀 AI Product Launch Blueprint\n\n".
                "Hello **{$userName}**, here is a structured 4-phase launch framework:\n\n".
                "1. **Phase 1: Pre-Launch & Positioning (Weeks 1-2)**\n".
                "   - Define Core Value Proposition: Address specific user pain points.\n".
                "   - Build VIP Waitlist Landing Page with referral loops.\n".
                "   - Alpha testing with 25-50 power users for feedback loops.\n\n".
                "2. **Phase 2: Momentum & Community (Weeks 3-4)**\n".
                "   - Founder-led product demos and short-form video explainers.\n".
                "   - Public Beta rollout with early-bird lifetime tiers.\n\n".
                "3. **Phase 3: Launch Day Execution**\n".
                "   - Product Hunt, Twitter/X thread, and LinkedIn announcement.\n".
                "   - Real-time customer support & live system performance monitoring.\n\n".
                "4. **Phase 4: Post-Launch Growth (30 Days)**\n".
                "   - Review churn, Net Promoter Score (NPS), and activation metrics.\n".
                "   - Deploy weekly customer-driven feature sprints.";
        }

        if (str_contains($lower, 'closure') || str_contains($lower, 'javascript')) {
            return "### 💡 JavaScript Closures Simplified\n\n".
                "A **Closure** is when an inner function remembers and has access to its outer function's variables, even after the outer function has finished executing.\n\n".
                "#### 🎒 Backpack Analogy\n".
                "Think of a function as a traveler. When the traveler leaves home (the outer function finishes), they pack a **backpack** containing all local variables they might need later. Whenever they run, they can reach into their backpack!\n\n".
                "```javascript\n".
                "function createCounter() {\n".
                "    let count = 0; // In the closure 'backpack'\n".
                "    return function() {\n".
                "        count++;\n".
                "        return count;\n".
                "    };\n".
                "}\n\n".
                "const counter = createCounter();\n".
                "console.log(counter()); // 1\n".
                "console.log(counter()); // 2\n".
                "```\n\n".
                "- **Key Benefit**: Data privacy and encapsulation without global state pollution.";
        }

        if (str_contains($lower, 'learning') || str_contains($lower, 'system design') || str_contains($lower, 'schedule')) {
            return "### 📚 4-Week Frontend System Design Mastery\n\n".
                "Here is an actionable weekly learning schedule for **{$userName}**:\n\n".
                "- **Week 1: Foundations & Architecture**\n".
                "  - Client vs Server Rendering (SSR, CSR, SSG, ISR, Hydration).\n".
                "  - Micro-frontends vs Monoliths, module federation.\n".
                "- **Week 2: State Management & Data Layer**\n".
                "  - Server State (React Query / SWR) vs Client State (Zustand / Redux).\n".
                "  - Real-time protocols: WebSockets, Server-Sent Events (SSE).\n".
                "- **Week 3: Performance & Reliability**\n".
                "  - Core Web Vitals (LCP, INP, CLS), code-splitting, tree-shaking.\n".
                "  - Asset caching (Service Workers, CDN edge caching).\n".
                "- **Week 4: Mock Interview & Real-world Systems**\n".
                "  - Design Google Docs (CRDT/OT) or Spotify Web Player.\n".
                "  - Accessibility (WCAG), internationalization (i18n), and error boundaries.";
        }

        if (str_contains($lower, 'review') || str_contains($lower, 'code')) {
            return "### 🔍 Code Quality & Architecture Review Checklist\n\n".
                "To review your code effectively, please paste your snippet! In the meantime, here is our 5-pillar review standard:\n\n".
                "1. **Performance**: Look out for memory leaks, unmemoized expensive calculations, and synchronous blocking calls.\n".
                "2. **Accessibility (a11y)**: Semantic HTML elements, proper `aria-*` tags, keyboard navigability.\n".
                "3. **Security**: Sanitized inputs against XSS/CSRF, safe state mutations, and zero credential leakage.\n".
                "4. **Maintainability**: Single Responsibility Principle (SRP), clean separation of UI and business logic.\n".
                "5. **Error Handling**: Graceful degradation, try-catch guards, and fallback states.";
        }

        if (str_contains($lower, 'arbitrage') || str_contains($lower, 'quantum') || str_contains($lower, 'crypto') || str_contains($lower, 'trading')) {
            return "### ⚡ Quantum AI Arbitrage Intelligence\n\n".
                "Hello **{$userName}**, here is how the Quantum Arbitrage engine works:\n\n".
                "- **High-Frequency Scanning**: Continuously scans 40+ premier cryptocurrency exchange order books every 30 milliseconds.\n".
                "- **Zero Market Exposure**: Executes simultaneous buy/sell pairs across order-book discrepancies, locking in guaranteed micro-spreads.\n".
                "- **Sentinel Capital Shield**: Autonomous MEV front-running defense and anti-slippage safeguards protect liquidity at all times.";
        }

        return "Hello **{$userName}**! I am your **Project AI Intelligence Workspace**.\n\n".
            "I can assist you with:\n".
            "- **Strategy & Planning**: Launch roadmaps, business plans, and market analysis.\n".
            "- **Full-Stack Engineering**: System architecture, code reviews, debugging, and algorithms.\n".
            "- **Quantum Trading & Analytics**: Platform ROI tiers, arbitrage mechanics, and financial intelligence.\n\n".
            "How can I help you take your next project further today?";
    }

    /**
     * Safely aggregate member incomes using Schema::hasTable checks.
     */
    private function getMemberIncomes(string $memberCode, ?string $date = null, ?string $start = null, ?string $end = null): array
    {
        $directQuery = Schema::hasTable('direct_incomes') ? DirectIncome::where('memberid', $memberCode) : null;
        $levelQuery = Schema::hasTable('level_incomes') ? LevelIncome::where('memberid', $memberCode) : null;
        $stakingQuery = Schema::hasTable('staking_incomes') ? StakingIncome::where('memberid', $memberCode) : null;
        $rewardQuery = Schema::hasTable('reward_incomes') ? RewardIncome::where('memberid', $memberCode) : null;

        if ($date) {
            $directQuery?->whereDate('created_at', $date);
            $levelQuery?->whereDate('created_at', $date);
            $stakingQuery?->whereDate('created_at', $date);
            $rewardQuery?->whereDate('created_at', $date);
        } elseif ($start && $end) {
            $directQuery?->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']);
            $levelQuery?->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']);
            $stakingQuery?->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']);
            $rewardQuery?->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']);
        }

        $direct = $directQuery ? (float) $directQuery->sum('amount') : 0.0;
        $level = $levelQuery ? (float) $levelQuery->sum('amount') : 0.0;
        $staking = $stakingQuery ? (float) $stakingQuery->sum('amount') : 0.0;
        $reward = $rewardQuery ? (float) $rewardQuery->sum('amount') : 0.0;

        return [
            'direct' => $direct,
            'level' => $level,
            'staking' => $staking,
            'reward' => $reward,
            'total' => $direct + $level + $staking + $reward,
        ];
    }

    /**
     * Show the dedicated Plan Explainer AI Voice Assistant page.
     */
    public function planExplainer(Request $request)
    {
        $memberId = session('MEMBER_ID');
        $member = $memberId ? MemberDetail::where('memberid', $memberId)->orWhere('id', $memberId)->first() : null;

        $planFacts = $this->getOfficialPlanData();
        $planDocument = $this->loadBusinessPlanDocument();

        return view('frontend.ai.plan-explainer', [
            'data' => $member ?? (object) [
                'name' => session('MEMBER_NAME', 'Guest Investor'),
                'email' => session('MEMBER_EMAIL', 'guest@quantum-ai.com'),
                'mobile' => session('MEMBER_MOBILE', ''),
                'profile_image' => null,
            ],
            'isLoggedIn' => (bool) $member,
            'planFacts' => $planFacts,
            'planDocument' => $planDocument,
        ]);
    }

    /**
     * Get the current active business plan document.
     */
    public function getPlanDocument()
    {
        return response()->json([
            'success' => true,
            'document' => $this->loadBusinessPlanDocument(),
        ]);
    }

    /**
     * Update the business plan document.
     */
    public function updatePlanDocument(Request $request)
    {
        $request->validate([
            'document' => 'required|string|max:100000',
        ]);

        $content = trim((string) $request->input('document'));
        $path = storage_path('app/business_plan_document.txt');
        @file_put_contents($path, $content);

        return response()->json([
            'success' => true,
            'message' => 'Document saved successfully! Plan Explainer AI will now explain strictly using this document.',
        ]);
    }

    /**
     * Get structured sequential speech parts from the business plan document.
     * Allows continuous, non-stop voice explanation part by part.
     */
    public function getPlanSpeech(Request $request)
    {
        $language = strtolower(trim((string) $request->input('language', 'hi'))); // 'hi' or 'en'
        $document = $this->loadBusinessPlanDocument();
        $lines = $this->buildDocumentLines($document, $language);

        return response()->json([
            'success' => true,
            'language' => $language,
            'total_lines' => count($lines),
            'lines' => $lines,
            'document_raw' => $document,
            'document_title' => 'Quantum ARB Official Compensation Plan',
        ]);
    }


    /**
     * Upload and extract business plan document (PDF or TXT).
     */
    public function uploadPlanDocument(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:25600', // max 25MB
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $origName = $file->getClientOriginalName();

        if (! in_array($ext, ['pdf', 'txt'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file format. Only .pdf and .txt files are supported.',
            ], 422);
        }

        $extractedText = '';

        if ($ext === 'txt') {
            $extractedText = (string) file_get_contents($file->getRealPath());
            if (empty(trim($extractedText))) {
                return response()->json([
                    'success' => false,
                    'message' => 'The uploaded TXT file is empty.',
                ], 422);
            }
        } elseif ($ext === 'pdf') {
            $pdfBytes = file_get_contents($file->getRealPath());
            if (! $pdfBytes || strlen($pdfBytes) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'The uploaded PDF file is empty or unreadable.',
                ], 422);
            }

            // Save backup of original PDF
            $pdfPath = storage_path('app/business_plan_document.pdf');
            @file_put_contents($pdfPath, $pdfBytes);

            // Use Gemini to extract comprehensive compensation plan text from the PDF
            try {
                $apiKey = config('gemini.api_key');
                if (! $apiKey) {
                    throw new \Exception('Gemini API key is not configured.');
                }

                $prompt = "You are a specialized business plan & compensation document extractor.\n"
                    ."Read the entire attached PDF business plan document carefully.\n"
                    ."Extract ALL package details, investment tiers, daily ROI percentages, direct referral bonus percentages, "
                    ."unilevel / matching bonuses for all levels, safety capping rules (3X, 5X or others), leadership ranks & qualification criteria, "
                    ."and deposit/withdrawal conditions.\n"
                    ."Format the extracted plan into clean, comprehensive, highly-structured plain text with clear headings and bullet points.\n"
                    ."Preserve all exact figures, percentages, limits, and rules verbatim without omitting or assuming anything.";

                $response = Http::withHeaders(['Content-Type' => 'application/json', 'x-goog-api-key' => $apiKey])
                    ->timeout(60)
                    ->post('https://generativelanguage.googleapis.com/v1beta/models/'.config('gemini.default_model', 'gemini-1.5-flash').':generateContent', [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $prompt],
                                    [
                                        'inlineData' => [
                                            'mimeType' => 'application/pdf',
                                            'data' => base64_encode($pdfBytes),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1,
                            'maxOutputTokens' => 4500,
                        ],
                    ]);

                if ($response->successful()) {
                    $resData = $response->json();
                    $extractedText = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '';
                } else {
                    Log::error('PDF Gemini Extraction Failed: '.$response->body());
                    throw new \Exception('Gemini extraction failed: '.$response->status());
                }
            } catch (\Exception $e) {
                Log::error('PDF Extraction Error: '.$e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'PDF Extraction failed: '.$e->getMessage().'. Please ensure the PDF is not password-protected, or paste the text directly.',
                ], 500);
            }
        }

        $extractedText = trim($extractedText);
        if (empty($extractedText)) {
            return response()->json([
                'success' => false,
                'message' => 'No readable text content could be extracted from this document.',
            ], 422);
        }

        // Save to storage/app/business_plan_document.txt
        $dest = storage_path('app/business_plan_document.txt');
        @file_put_contents($dest, $extractedText);

        return response()->json([
            'success' => true,
            'message' => 'Plan document uploaded and processed successfully! Plan Explainer AI has updated its knowledge base.',
            'filename' => $origName,
            'file_type' => strtoupper($ext),
            'characters' => strlen($extractedText),
            'preview' => mb_substr($extractedText, 0, 400).'...',
        ]);
    }

    /**
     * Interactive Voice AI API handler for Plan Explainer.
     */
    public function planExplainerChat(Request $request)
    {
        $query = trim((string) ($request->input('message') ?: $request->input('spoken_message') ?: ''));
        if ($query === '') {
            $query = 'Quantum ARB business plan overview';
        }
        $spokenQuery = trim((string) $request->input('spoken_message', ''));
        $inputAmount = $request->input('amount') ? (float) $request->input('amount') : null;
        $language = strtolower(trim((string) $request->input('language', 'hi'))); // 'hi' or 'en'
        $cleanQuery = mb_strtolower($query.' '.$spokenQuery, 'UTF-8');

        // Check if query contains an explicit dollar or number for ROI calculation
        if (! $inputAmount && preg_match('/(?:\$|\b(?:invest|package|dollar|amount|pe|me)\s*)?([0-9]{2,7})\b/', $cleanQuery, $m)) {
            $val = (float) $m[1];
            if ($val >= 50) {
                $inputAmount = $val;
            }
        }

        $planData = $this->getOfficialPlanData();
        $planDocument = $this->loadBusinessPlanDocument();
        $intent = $this->detectPlanIntent($cleanQuery, $inputAmount);

        $langInstructions = ($language === 'en')
            ? "Reply in natural, warm, charismatic, and professional English. Sound like an experienced human business mentor explaining face-to-face. Keep sentences smooth, crystal-clear, and pleasant for speech synthesis."
            : "Reply in warm, friendly, natural conversational Hindi / Hinglish. Sound like an enthusiastic human leader explaining directly to a friend. Keep sentences clear, natural, and melodious for speech synthesis.";

        // 1. Try Gemini with comprehensive system prompt
        try {
            $apiKey = config('gemini.api_key');
            if (! $apiKey) {
                throw new \Exception('Gemini API key not configured');
            }

            $systemInstruction = "You are 'Plan Explainer AI', a warm, articulate, and completely human-like senior business plan specialist for Quantum ARB. "
                ."CRITICAL RULE: You MUST explain the business plan STRICTLY AND ONLY using the facts from the verified official document provided below. "
                ."DO NOT invent, assume, or fabricate any rules, packages, or numbers not stated in this document. "
                ."Explain like a real human consultant: friendly, transparent, encouraging, and clear without robotic lists or jargon. "
                ."LANGUAGE RULE: {$langInstructions}\n\n"
                ."=== OFFICIAL PLAN DOCUMENT ===\n"
                .$planDocument."\n"
                ."=== END OF DOCUMENT ===\n\n"
                ."Detected topic/intent: '{$intent}'. If the user asks for investment calculation or specific amount return, calculate exact numbers from the package rates in the document.";

            $response = Http::withHeaders(['Content-Type' => 'application/json', 'x-goog-api-key' => $apiKey])
                ->timeout(5)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/'.config('gemini.default_model', 'gemini-1.5-flash').':generateContent', [
                    'systemInstruction' => ['parts' => [['text' => $systemInstruction]]],
                    'contents' => [
                        ['role' => 'user', 'parts' => [['text' => $spokenQuery !== '' ? $query."\nSpoken input: ".$spokenQuery : $query]]],
                    ],
                    'generationConfig' => ['temperature' => 0.25, 'maxOutputTokens' => 450],
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $answer = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($answer) {
                    $cleanedAnswer = trim($answer);
                    $spoken = trim(preg_replace('/[*_#`\[\]]/', '', strip_tags($cleanedAnswer)));
                    $spoken = preg_replace('/\s+/', ' ', $spoken);

                    return response()->json([
                        'success' => true,
                        'intent' => $intent,
                        'language' => $language,
                        'answer' => $cleanedAnswer,
                        'spoken_text' => $spoken,
                    ]);
                }
            }
            throw new \Exception('Gemini unavailable');
        } catch (\Exception $e) {
            // Intelligent fallback generator
            $answer = $this->buildPlanFallbackResponse($intent, $cleanQuery, $inputAmount, $planData, $language);
            $spoken = trim(preg_replace('/[*_#`\[\]]/', '', strip_tags($answer)));
            $spoken = preg_replace('/\s+/', ' ', $spoken);

            return response()->json([
                'success' => true,
                'intent' => $intent,
                'language' => $language,
                'answer' => $answer,
                'spoken_text' => $spoken,
            ]);
        }
    }

    /**
     * Load business plan document text permanently from Sentinel Guard AI's project.pdf.
     */
    private function loadBusinessPlanDocument(): string
    {
        // 1. Permanent source: Sentinel Guard AI project.pdf extracted text
        $projectTxt = public_path('logo/project_extracted.txt');
        if (file_exists($projectTxt)) {
            $content = file_get_contents($projectTxt);
            if (! empty(trim($content))) {
                return trim($content);
            }
        }

        $projectDoc = public_path('logo/project');
        if (file_exists($projectDoc)) {
            $content = file_get_contents($projectDoc);
            if (! empty(trim($content))) {
                return trim($content);
            }
        }

        // 2. Storage backup
        $path = storage_path('app/business_plan_document.txt');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if (! empty(trim($content))) {
                return trim($content);
            }
        }

        return "Quantum ARB compensation plan: Starter ($100-$999: 1.5% daily), Standard ($1K-$5K: 2%), Advance ($5K-$10K: 2.5%), Elite ($10K-$25K: 3%), Master ($25K-$100K: 3.5%), VIP ($100K+: 4%). 5% direct commission. 20 levels ROI matching (L1: 15%, L2: 10%, L3-5: 5%, L6-10: 3%, L11-20: 2%). 3X capping basic, 5X with 1 direct.";
    }

    /**
     * Build natural, continuous line-by-line speech and display data from the business plan document.
     */
    private function buildDocumentLines(string $document, string $language): array
    {
        $isHindi = ($language === 'hi');
        $isVerifiedStandardDoc = str_contains($document, 'QUANTUM ARB') && str_contains($document, 'DIRECT REFERRAL') && str_contains($document, '20-LEVEL');

        if ($isVerifiedStandardDoc) {
            if ($isHindi) {
                return [
                    ['id' => 1, 'type' => 'heading', 'text' => '# QUANTUM ARB - ऑफिशियल बिजनेस और कंपनसेशन प्लान', 'spoken' => 'क्वांटम एआरबी - ऑफिशियल बिजनेस और कंपनसेशन प्लान।'],
                    ['id' => 2, 'type' => 'subheading', 'text' => '## 1. प्लेटफ़ॉर्म ओवरव्यू', 'spoken' => '1. प्लेटफ़ॉर्म ओवरव्यू।'],
                    ['id' => 3, 'type' => 'paragraph', 'text' => 'क्वांटम एआरबी एक ऑटोनॉमस एल्गोरिद्मिक आर्बिट्राज और लिक्विडिटी यील्ड प्लेटफ़ॉर्म है।', 'spoken' => 'क्वांटम एआरबी एक ऑटोनॉमस एल्गोरिद्मिक आर्बिट्राज और लिक्विडिटी यील्ड प्लेटफ़ॉर्म है।'],
                    ['id' => 4, 'type' => 'paragraph', 'text' => 'यह 24/7 प्रमुख क्रिप्टो एक्सचेंजों जैसे Binance, OKX, Bybit और Coinbase पर रिस्क-फ्री आर्बिट्राज स्कैनिंग करता है।', 'spoken' => 'यह 24 घंटे प्रमुख क्रिप्टो एक्सचेंजों जैसे बाइनेंस, ओकेएक्स, बायबिट और कॉइनबेस के बीच रिस्क-फ्री आर्बिट्राज स्कैनिंग करता है।'],
                    ['id' => 5, 'type' => 'paragraph', 'text' => 'सभी ट्रांजैक्शन और पेआउट USD ($) में कैलकुलेट होते हैं और USDT (BEP-20 / TRC-20) के जरिए प्रोसेस किए जाते हैं।', 'spoken' => 'सभी ट्रांजैक्शन और पेआउट यूएसडी में कैलकुलेट होते हैं और यूएसडीटी बीईपी 20 और टीआरसी 20 के जरिए प्रोसेस किए जाते हैं।'],
                    
                    ['id' => 6, 'type' => 'subheading', 'text' => '## 2. इन्वेस्टमेंट पैकेज और डेली स्टेकिंग आर ओ आई (ROI)', 'spoken' => '2. इन्वेस्टमेंट पैकेज और डेली स्टेकिंग आर ओ आई।'],
                    ['id' => 7, 'type' => 'paragraph', 'text' => 'हर मेंबर मिनिमम $100 से इन्वेस्टमेंट पैकेज एक्टिवेट कर सकता है।', 'spoken' => 'हर मेंबर मिनिमम 100 डॉलर से इन्वेस्टमेंट पैकेज एक्टिवेट कर सकता है।'],
                    ['id' => 8, 'type' => 'paragraph', 'text' => 'डेली प्रॉफिट हर 24 घंटे में सीधे आपके वॉलेट में क्रेडिट होता है।', 'spoken' => 'डेली प्रॉफिट हर 24 घंटे में सीधे आपके वॉलेट में क्रेडिट होता है।'],
                    ['id' => 9, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• Starter Tier ($100 to $999): 1.5% Daily ROI (लगभग 45% Monthly)', 'spoken' => 'पहला, स्टार्टर टियर: 100 से 999 डॉलर पर 1.5% डेली आर ओ आई यानी लगभग 45% मंथली रिटर्न।'],
                    ['id' => 10, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• Standard Tier ($1,000 to $4,999): 2.0% Daily ROI (लगभग 60% Monthly)', 'spoken' => 'दूसरा, स्टैंडर्ड टियर: 1000 से 4999 डॉलर पर 2.0% डेली आर ओ आई यानी 60% मंथली रिटर्न।'],
                    ['id' => 11, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• Advance Tier ($5,000 to $9,999): 2.5% Daily ROI (लगभग 75% Monthly)', 'spoken' => 'तीसरा, एडवांस टियर: 5000 से 9999 डॉलर पर 2.5% डेली आर ओ आई यानी 75% मंथली रिटर्न।'],
                    ['id' => 12, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• Elite Tier ($10,000 to $24,999): 3.0% Daily ROI (लगभग 90% Monthly)', 'spoken' => 'चौथा, एलीट टियर: 10000 से 24999 डॉलर पर 3.0% डेली आर ओ आई यानी 90% मंथली रिटर्न।'],
                    ['id' => 13, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• Master Tier ($25,000 to $99,999): 3.5% Daily ROI (लगभग 105% Monthly)', 'spoken' => 'पाँचवाँ, मास्टर टियर: 25000 से 99999 डॉलर पर 3.5% डेली आर ओ आई यानी 105% मंथली रिटर्न।'],
                    ['id' => 14, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• VIP Whale Tier ($100,000 and above): 4.0% Daily ROI (लगभग 120% Monthly)', 'spoken' => 'और छठा, वीआईपी व्हेल टियर: 1 लाख डॉलर और ऊपर पर 4.0% डेली आर ओ आई यानी 120% तक मंथली रिटर्न मिलता है।'],

                    ['id' => 15, 'type' => 'subheading', 'text' => '## 3. डायरेक्ट रेफरल कमीशन (5% इंस्टेंट)', 'spoken' => '3. डायरेक्ट रेफरल कमीशन।'],
                    ['id' => 16, 'type' => 'bullet', 'topic' => 'direct_bonus', 'text' => '• हर डायरेक्ट स्पॉन्सर मेंबर के पैकेज अमाउंट पर 5% का फ्लैट इंस्टेंट डायरेक्ट बोनस तुरंत वॉलेट में मिलता है।', 'spoken' => 'हर डायरेक्ट स्पॉन्सर मेंबर के पैकेज अमाउंट पर आपको 5% का फ्लैट इंस्टेंट डायरेक्ट बोनस तुरंत वॉलेट में मिलता है।'],
                    ['id' => 17, 'type' => 'bullet', 'topic' => 'direct_bonus', 'text' => '• उदाहरण: अगर आपका डायरेक्ट मेंबर $2,000 से एक्टिवेट करता है, तो आपको तुरंत $100 मिल जाते हैं।', 'spoken' => 'उदाहरण के लिए, अगर आपका डायरेक्ट मेंबर 2000 डॉलर से एक्टिवेट करता है, तो आपको तुरंत 100 डॉलर मिल जाते हैं।'],
                    ['id' => 18, 'type' => 'bullet', 'topic' => 'direct_bonus', 'text' => '• डायरेक्ट रेफरल्स की कोई सीमा नहीं है, आप जितने चाहें उतने मेंबर्स जोड़ सकते हैं।', 'spoken' => 'डायरेक्ट रेफरल की कोई लिमिट नहीं है, आप जितने चाहें उतने डायरेक्ट मेंबर्स जोड़ सकते हैं।'],
                    ['id' => 19, 'type' => 'bullet', 'topic' => 'direct_bonus', 'text' => '• कम से कम 1 डायरेक्ट रेफरल एक्टिव होते ही आपकी कैपिंग 3X से बढ़कर 5X बूस्टर में अपग्रेड हो जाती है।', 'spoken' => 'कम से कम 1 डायरेक्ट स्पॉन्सर होने पर आपकी कैपिंग 3X से बढ़कर 5X बूस्टर कैपिंग में अपग्रेड हो जाती है।'],

                    ['id' => 20, 'type' => 'subheading', 'text' => '## 4. 20-लेवल आर ओ आई जेनरेशन मैचिंग इनकम', 'spoken' => '4. 20-लेवल आर ओ आई टीम मैचिंग इनकम।'],
                    ['id' => 21, 'type' => 'paragraph', 'text' => 'आप अपनी पूरी टीम के डेली ROI का एक निश्चित प्रतिशत 20 जनरेशन लेवल तक प्राप्त करते हैं:', 'spoken' => 'आप अपनी पूरी टीम के डेली आर ओ आई का एक निश्चित प्रतिशत 20 लेवल तक हर रोज़ प्राप्त करते हैं।'],
                    ['id' => 22, 'type' => 'bullet', 'topic' => 'level_matching', 'text' => '• Level 1: डायरेक्ट मेंबर्स के डेली ROI का 15%', 'spoken' => 'लेवल 1 पर डायरेक्ट मेंबर्स के डेली आर ओ आई का 15% मिलता है।'],
                    ['id' => 23, 'type' => 'bullet', 'topic' => 'level_matching', 'text' => '• Level 2: लेवल 2 टीम मेंबर्स के डेली ROI का 10%', 'spoken' => 'लेवल 2 पर लेवल 2 टीम के डेली आर ओ आई का 10% मिलता है।'],
                    ['id' => 24, 'type' => 'bullet', 'topic' => 'level_matching', 'text' => '• Level 3 से Level 5: लेवल 3 से 5 तक 5% डेली ROI मैचिंग', 'spoken' => 'लेवल 3 से 5 तक 5% डेली मैचिंग बोनस मिलता है।'],
                    ['id' => 25, 'type' => 'bullet', 'topic' => 'level_matching', 'text' => '• Level 6 से Level 10: लेवल 6 से 10 तक 3% डेली ROI मैचिंग', 'spoken' => 'लेवल 6 से 10 तक 3% डेली मैचिंग बोनस मिलता है।'],
                    ['id' => 26, 'type' => 'bullet', 'topic' => 'level_matching', 'text' => '• Level 11 से Level 20: लेवल 11 से 20 तक 2% डेली ROI मैचिंग', 'spoken' => 'और लेवल 11 से लेवल 20 तक 2% डेली मैचिंग बोनस मिलता है। यह मैचिंग बोनस हर रोज़ तब तक मिलता है जब तक टीम को डेली आर ओ आई मिलता रहता है।'],

                    ['id' => 27, 'type' => 'subheading', 'text' => '## 5. 3X और 5X सेफ़्टी कैपिंग मल्टीप्लायर रूल्स', 'spoken' => '5. 3X और 5X सेफ़्टी कैपिंग रूल्स।'],
                    ['id' => 28, 'type' => 'bullet', 'topic' => 'capping_booster', 'text' => '• बेसिक 3X (300%) Capping: यदि मेंबर का कोई डायरेक्ट रेफरल नहीं है (Direct = 0)।', 'spoken' => 'यदि किसी मेंबर का कोई डायरेक्ट नहीं है, तो उनकी बेसिक कैपिंग 3X यानी 300% होती है।'],
                    ['id' => 29, 'type' => 'bullet', 'topic' => 'capping_booster', 'text' => '• बूस्टर 5X (500%) Capping: जैसे ही आपका 1 या अधिक एक्टिव डायरेक्ट मेंबर हो जाता है (Direct >= 1)।', 'spoken' => 'लेकिन जैसे ही आप सिर्फ 1 एक्टिव डायरेक्ट मेंबर जोड़ लेते हैं, आपकी अर्निंग लिमिट 5X यानी 500% बूस्टर कैपिंग हो जाती है।'],
                    ['id' => 30, 'type' => 'bullet', 'topic' => 'capping_booster', 'text' => '• सभी इनकम्स (Direct, Level, Staking, Rewards) कैपिंग लिमिट में गिनी जाती हैं।', 'spoken' => 'आपकी सभी इनकम्स कैपिंग में गिनी जाती हैं।'],
                    ['id' => 31, 'type' => 'bullet', 'topic' => 'capping_booster', 'text' => '• कैपिंग पूरी होने पर आप री-टॉपअप करके नया अर्निंग साइकिल शुरू कर सकते हैं।', 'spoken' => 'और कैपिंग पूरी होने पर आप री-टॉपअप करके नया अर्निंग साइकिल शुरू कर सकते हैं।'],

                    ['id' => 32, 'type' => 'subheading', 'text' => '## 6. लीडरशिप प्रोग्रेसिव रैंक्स और स्पेशल ट्रेडिंग बोनस', 'spoken' => '6. लीडरशिप रैंक्स और स्पेशल ट्रेडिंग बोनस।'],
                    ['id' => 33, 'type' => 'bullet', 'topic' => 'leadership_ranks', 'text' => '• Silver Rank: 15% Special Trading Bonus', 'spoken' => 'सिल्वर रैंक पर 15% स्पेशल ट्रेडिंग बोनस मिलता है।'],
                    ['id' => 34, 'type' => 'bullet', 'topic' => 'leadership_ranks', 'text' => '• Gold Rank: 30% Special Trading Bonus', 'spoken' => 'गोल्ड रैंक पर 30% स्पेशल ट्रेडिंग बोनस मिलता है।'],
                    ['id' => 35, 'type' => 'bullet', 'topic' => 'leadership_ranks', 'text' => '• Diamond Rank: 40% Special Trading Bonus', 'spoken' => 'और डायमंड रैंक अचीव करने पर 40% स्पेशल ट्रेडिंग बोनस का बड़ा लाभ मिलता है।'],

                    ['id' => 36, 'type' => 'subheading', 'text' => '## 7. डिपॉज़िट, वॉलेट और विथड्रॉल नियम', 'spoken' => '7. डिपॉज़िट, वॉलेट और विथड्रॉल नियम।'],
                    ['id' => 37, 'type' => 'bullet', 'topic' => 'deposit_withdraw', 'text' => '• मिनिमम एक्टिवेशन: $100 USDT (BEP-20 / TRC-20)।', 'spoken' => 'मिनिमम एक्टिवेशन सिर्फ 100 डॉलर यूएसडीटी है।'],
                    ['id' => 38, 'type' => 'bullet', 'topic' => 'deposit_withdraw', 'text' => '• 3 वॉलेट्स: Cash Wallet (Payouts), P2P Wallet (Instant Transfers), Staking Wallet (Yield)।', 'spoken' => 'प्लेटफ़ॉर्म पर तीन मुख्य वॉलेट्स हैं: कैश वॉलेट विथड्रॉल के लिए, पी टू पी वॉलेट इंस्टेंट ट्रांसफर के लिए, और स्टेकिंग वॉलेट एक्टिव यील्ड के लिए।'],
                    ['id' => 39, 'type' => 'bullet', 'topic' => 'deposit_withdraw', 'text' => '• विथड्रॉल: 24/7 ऑटोमेटेड ऑन-चेन प्रोसेसिंग।', 'spoken' => 'सभी विथड्रॉल 24 घंटे सातों दिन ऑटोमेटेड ऑन-चेन प्रोसेस किए जाते हैं। धन्यवाद!'],
                ];
            } else {
                return [
                    ['id' => 1, 'type' => 'heading', 'text' => '# QUANTUM ARB - OFFICIAL BUSINESS & COMPENSATION PLAN DOCUMENT', 'spoken' => 'Quantum ARB official business and compensation plan document.'],
                    ['id' => 2, 'type' => 'subheading', 'text' => '## 1. PLATFORM OVERVIEW', 'spoken' => '1. Platform overview.'],
                    ['id' => 3, 'type' => 'paragraph', 'text' => 'Quantum ARB is an autonomous algorithmic arbitrage and liquidity yield platform.', 'spoken' => 'Quantum ARB is an autonomous algorithmic arbitrage and liquidity yield platform.'],
                    ['id' => 4, 'type' => 'paragraph', 'text' => 'It scans global exchange order books 24/7 to capture risk-free price discrepancies across premier cryptocurrency exchanges (Binance, OKX, Bybit, Coinbase, Kraken, etc.).', 'spoken' => 'It scans global exchange order books 24/7 to capture risk-free price discrepancies across premier cryptocurrency exchanges including Binance, OKX, Bybit, Coinbase, and Kraken.'],
                    ['id' => 5, 'type' => 'paragraph', 'text' => 'All transactions, earnings, and payouts are calculated in USD ($) and processed via USDT (BEP-20 / TRC-20).', 'spoken' => 'All transactions, earnings, and payouts are calculated in USD and processed via USDT on BEP-20 and TRC-20 networks.'],

                    ['id' => 6, 'type' => 'subheading', 'text' => '## 2. INVESTMENT PACKAGES & DAILY ARBITRAGE ROI (STAKING YIELD)', 'spoken' => '2. Investment packages and daily arbitrage staking yield.'],
                    ['id' => 7, 'type' => 'paragraph', 'text' => 'Every member can activate an investment package starting from a minimum of $100.', 'spoken' => 'Every member can activate an investment package starting from a minimum of 100 dollars.'],
                    ['id' => 8, 'type' => 'paragraph', 'text' => 'Daily ROI is credited every 24 hours directly into the member\'s wallet.', 'spoken' => 'Daily ROI is credited every 24 hours directly into the member wallet.'],
                    ['id' => 9, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• Starter Tier ($100 to $999): 1.5% Daily ROI (approx. 45% Monthly Return)', 'spoken' => 'Starter Tier from 100 to 999 dollars earns 1.5% daily ROI, approximately 45% monthly.'],
                    ['id' => 10, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• Standard Tier ($1,000 to $4,999): 2.0% Daily ROI (approx. 60% Monthly Return)', 'spoken' => 'Standard Tier from 1,000 to 4,999 dollars earns 2.0% daily ROI, approximately 60% monthly.'],
                    ['id' => 11, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• Advance Tier ($5,000 to $9,999): 2.5% Daily ROI (approx. 75% Monthly Return)', 'spoken' => 'Advance Tier from 5,000 to 9,999 dollars earns 2.5% daily ROI, approximately 75% monthly.'],
                    ['id' => 12, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• Elite Tier ($10,000 to $24,999): 3.0% Daily ROI (approx. 90% Monthly Return)', 'spoken' => 'Elite Tier from 10,000 to 24,999 dollars earns 3.0% daily ROI, approximately 90% monthly.'],
                    ['id' => 13, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• Master Tier ($25,000 to $99,999): 3.5% Daily ROI (approx. 105% Monthly Return)', 'spoken' => 'Master Tier from 25,000 to 99,999 dollars earns 3.5% daily ROI, approximately 105% monthly.'],
                    ['id' => 14, 'type' => 'bullet', 'topic' => 'daily_roi', 'text' => '• VIP Whale Tier ($100,000 and above): 4.0% Daily ROI (approx. 120% Monthly Return)', 'spoken' => 'And VIP Whale Tier from 100,000 dollars and above earns 4.0% daily ROI, or up to 120% monthly profit.'],

                    ['id' => 15, 'type' => 'subheading', 'text' => '## 3. DIRECT REFERRAL COMMISSION (5% INSTANT)', 'spoken' => '3. Direct referral commission.'],
                    ['id' => 16, 'type' => 'bullet', 'topic' => 'direct_bonus', 'text' => '• On every member you directly sponsor with your referral link, you receive a flat 5% instant Direct Referral Bonus of their package amount.', 'spoken' => 'On every member you directly sponsor with your referral link, you receive a flat 5% instant direct referral bonus of their package amount.'],
                    ['id' => 17, 'type' => 'bullet', 'topic' => 'direct_bonus', 'text' => '• Example: If your direct member activates with $2,000, you instantly receive $100 in your wallet.', 'spoken' => 'For example, if your direct member activates with 2,000 dollars, you instantly receive 100 dollars in your wallet.'],
                    ['id' => 18, 'type' => 'bullet', 'topic' => 'direct_bonus', 'text' => '• There is no limit on the number of direct referrals you can make.', 'spoken' => 'There is no limit on the number of direct referrals you can make.'],
                    ['id' => 19, 'type' => 'bullet', 'topic' => 'direct_bonus', 'text' => '• Having at least 1 active direct referral also unlocks the 5X Capping Booster.', 'spoken' => 'Having at least 1 active direct referral also unlocks the 5X capping booster.'],

                    ['id' => 20, 'type' => 'subheading', 'text' => '## 4. 20-LEVEL ROI-TO-ROI GENERATION MATCHING INCOME', 'spoken' => '4. 20-level ROI matching income.'],
                    ['id' => 21, 'type' => 'paragraph', 'text' => 'Members earn a daily percentage based on the daily ROI earned by their team across 20 generation levels:', 'spoken' => 'Members earn an ongoing daily percentage based on the daily returns earned by their team across 20 levels.'],
                    ['id' => 22, 'type' => 'bullet', 'topic' => 'level_matching', 'text' => '• Level 1: 15% of the daily ROI earned by direct members.', 'spoken' => 'Level 1 earns 15% of the daily ROI earned by direct members.'],
                    ['id' => 23, 'type' => 'bullet', 'topic' => 'level_matching', 'text' => '• Level 2: 10% of the daily ROI earned by level 2 team members.', 'spoken' => 'Level 2 earns 10% of the daily ROI earned by level 2 team members.'],
                    ['id' => 24, 'type' => 'bullet', 'topic' => 'level_matching', 'text' => '• Level 3 to Level 5: 5% of the daily ROI earned by team members in levels 3, 4, and 5.', 'spoken' => 'Level 3 to Level 5 earns 5% of the daily ROI earned by team members.'],
                    ['id' => 25, 'type' => 'bullet', 'topic' => 'level_matching', 'text' => '• Level 6 to Level 10: 3% of the daily ROI earned by team members in levels 6 through 10.', 'spoken' => 'Level 6 to Level 10 earns 3% of the daily ROI earned by team members.'],
                    ['id' => 26, 'type' => 'bullet', 'topic' => 'level_matching', 'text' => '• Level 11 to Level 20: 2% of the daily ROI earned by team members in levels 11 through 20.', 'spoken' => 'Level 11 to Level 20 earns 2% of the daily ROI earned by team members.'],

                    ['id' => 27, 'type' => 'subheading', 'text' => '## 5. 3X AND 5X SAFETY CAPPING MULTIPLIER RULES', 'spoken' => '5. 3X and 5X safety capping multiplier rules.'],
                    ['id' => 28, 'type' => 'bullet', 'topic' => 'capping_booster', 'text' => '• Basic 3X Capping (300%): If a member has NO active direct downline (downline = 0).', 'spoken' => 'Basic 3X capping applies if a member has no active direct downline.'],
                    ['id' => 29, 'type' => 'bullet', 'topic' => 'capping_booster', 'text' => '• Booster 5X Capping (500%): As soon as a member has 1 or more active direct referrals (downline >= 1).', 'spoken' => 'Booster 5X capping unlocks as soon as a member has 1 or more active direct referrals.'],
                    ['id' => 30, 'type' => 'bullet', 'topic' => 'capping_booster', 'text' => '• All earnings (Direct, Level Matching, Daily ROI, Rank Rewards) count towards the capping limit.', 'spoken' => 'All earnings count towards the capping limit.'],
                    ['id' => 31, 'type' => 'bullet', 'topic' => 'capping_booster', 'text' => '• Once 3X or 5X capping is reached, the member can re-topup to start a fresh earning cycle.', 'spoken' => 'Once capping is reached, the member can re-topup to start a fresh earning cycle.'],

                    ['id' => 32, 'type' => 'subheading', 'text' => '## 6. LEADERSHIP PROGRESSIVE RANKS & SPECIAL TRADING BONUS', 'spoken' => '6. Leadership progressive ranks and special trading bonus.'],
                    ['id' => 33, 'type' => 'bullet', 'topic' => 'leadership_ranks', 'text' => '• Silver Rank: 15% Special Trading Bonus', 'spoken' => 'Silver Rank grants a 15% Special Trading Bonus.'],
                    ['id' => 34, 'type' => 'bullet', 'topic' => 'leadership_ranks', 'text' => '• Gold Rank: 30% Special Trading Bonus', 'spoken' => 'Gold Rank grants a 30% Special Trading Bonus.'],
                    ['id' => 35, 'type' => 'bullet', 'topic' => 'leadership_ranks', 'text' => '• Diamond Rank: 40% Special Trading Bonus', 'spoken' => 'Diamond Rank grants a 40% Special Trading Bonus on team turnover.'],

                    ['id' => 36, 'type' => 'subheading', 'text' => '## 7. DEPOSIT, WALLET & WITHDRAWAL RULES', 'spoken' => '7. Deposit, wallet, and withdrawal rules.'],
                    ['id' => 37, 'type' => 'bullet', 'topic' => 'deposit_withdraw', 'text' => '• Minimum Activation: $100 USDT (BEP-20 / TRC-20).', 'spoken' => 'Minimum activation is 100 USDT.'],
                    ['id' => 38, 'type' => 'bullet', 'topic' => 'deposit_withdraw', 'text' => '• Wallets: Cash Wallet (Payouts), P2P Wallet (Transfers), Staking Wallet (Yield).', 'spoken' => 'The platform provides Cash Wallet, P2P Wallet, and Staking Wallet.'],
                    ['id' => 39, 'type' => 'bullet', 'topic' => 'deposit_withdraw', 'text' => '• Withdrawals: Automated 24/7 processing to the member\'s verified wallet address.', 'spoken' => 'All withdrawals are processed automatically 24 hours a day, 7 days a week. Thank you.'],
                ];
            }
        }

        // Generic parser for any custom uploaded document (PDF or TXT)
        $rawLines = preg_split('/\r\n|\r|\n/', $document);
        $lines = [];
        $lineId = 1;

        foreach ($rawLines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;

            $type = 'paragraph';
            if (str_starts_with($trimmed, '# ')) $type = 'heading';
            elseif (str_starts_with($trimmed, '## ')) $type = 'subheading';
            elseif (str_starts_with($trimmed, '### ')) $type = 'subheading';
            elseif (str_starts_with($trimmed, '- ') || str_starts_with($trimmed, '* ') || str_starts_with($trimmed, '• ')) $type = 'bullet';

            $cleanSpoken = preg_replace('/[*_#`\[\]]/', '', $trimmed);
            $cleanSpoken = preg_replace('/^[-•*]\s*/', '', $cleanSpoken);
            $cleanSpoken = trim(preg_replace('/\s+/', ' ', $cleanSpoken));

            $lines[] = [
                'id' => $lineId++,
                'type' => $type,
                'text' => $trimmed,
                'spoken' => $cleanSpoken,
            ];
        }

        return $lines;
    }

    /**
     * Get official verified platform plan data.
     */
    private function getOfficialPlanData(): array
    {
        return [
            'platform' => 'Quantum ARB',
            'model' => 'Algorithmic Arbitrage Liquidity Yield & Multi-Tier Affiliate Ecosystem',
            'currency' => 'USD ($)',
            'min_investment' => 100,
            'deposit_assets' => 'USDT (BEP-20 / TRC-20)',
            'packages' => [
                ['tier' => 'Starter', 'range' => '$100 – $999', 'min' => 100, 'max' => 999, 'daily_roi' => 1.5, 'monthly_yield' => '45%'],
                ['tier' => 'Standard', 'range' => '$1,000 – $4,999', 'min' => 1000, 'max' => 4999, 'daily_roi' => 2.0, 'monthly_yield' => '60%'],
                ['tier' => 'Advance', 'range' => '$5,000 – $9,999', 'min' => 5000, 'max' => 9999, 'daily_roi' => 2.5, 'monthly_yield' => '75%'],
                ['tier' => 'Elite', 'range' => '$10,000 – $24,999', 'min' => 10000, 'max' => 24999, 'daily_roi' => 3.0, 'monthly_yield' => '90%'],
                ['tier' => 'Master', 'range' => '$25,000 – $99,999', 'min' => 25000, 'max' => 99999, 'daily_roi' => 3.5, 'monthly_yield' => '105%'],
                ['tier' => 'VIP Whale', 'range' => '$100,000+', 'min' => 100000, 'max' => null, 'daily_roi' => 4.0, 'monthly_yield' => '120%'],
            ],
            'direct_referral_percent' => 5,
            'level_income' => [
                ['level' => 1, 'rate' => 15, 'desc' => '15% on direct referrals\' daily ROI yield'],
                ['level' => 2, 'rate' => 10, 'desc' => '10% on level 2 team\'s daily ROI yield'],
                ['level' => '3 to 5', 'rate' => 5, 'desc' => '5% on level 3 to 5 team daily ROI yield'],
                ['level' => '6 to 10', 'rate' => 3, 'desc' => '3% on level 6 to 10 team daily ROI yield'],
                ['level' => '11 to 20', 'rate' => 2, 'desc' => '2% on level 11 to 20 team daily ROI yield'],
            ],
            'capping_rules' => [
                'basic' => '3X (300%) of self active investment without active direct referrals.',
                'booster' => '5X (500%) of self active investment unlocked with 1 or more active direct referrals.',
            ],
            'ranks' => [
                ['rank' => 'Silver', 'bonus' => '15% Special Trading Bonus'],
                ['rank' => 'Gold', 'bonus' => '30% Special Trading Bonus'],
                ['rank' => 'Diamond', 'bonus' => '40% Special Trading Bonus'],
            ],
            'wallets' => ['Cash Wallet', 'P2P Wallet', 'Staking Wallet'],
        ];
    }

    /**
     * Detect specific plan intent from user query.
     */
    private function detectPlanIntent(string $query, ?float $amount): string
    {
        if ($amount && $amount >= 50) {
            return 'calculator';
        }

        $keywords = [
            'calculator' => ['calculate', 'calc', 'karein to', 'invest karu', 'return kitna', 'profit kitna', 'milega', 'lagau to', 'hisab'],
            'roi_tiers' => ['roi', 'tier', 'daily', 'package', 'packages', 'percent', 'percentage', 'byaaj', 'kitna percent', 'monthly', 'rate'],
            'direct_income' => ['direct', 'referral', 'sponsor', 'direct income', 'direct commission', 'direct bonus', 'refer karne par'],
            'level_income' => ['level', 'generation', 'matching', 'level income', 'levels', '20 level', 'roi to roi', 'team income'],
            'capping' => ['capping', '3x', '5x', 'limit', 'booster', 'max income', 'capping limit', '3 guna', '5 guna', 'कैपिंग'],
            'ranks' => ['rank', 'silver', 'gold', 'diamond', 'reward', 'special trading', 'achiever', 'trading bonus'],
            'deposit_withdrawal' => ['deposit', 'withdraw', 'withdrawal', 'minimum', 'usdt', 'wallet', 'nikalna', 'p2p', 'activation'],
            'overview' => ['plan', 'business plan', 'explain', 'samjhao', 'overview', 'kaise kaam karta hai', 'quantum arb', 'full plan', 'about plan'],
        ];

        foreach ($keywords as $intent => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($query, $pattern)) {
                    return $intent;
                }
            }
        }

        return 'overview';
    }

    /**
     * Build rich, human-like fallback response for plan inquiries in Hindi or English.
     */
    private function buildPlanFallbackResponse(string $intent, string $query, ?float $amount, array $planData, string $language = 'hi'): string
    {
        $isEn = ($language === 'en');

        switch ($intent) {
            case 'calculator':
                $inv = $amount ?: 1000.0;
                $dailyRate = 1.5;
                if ($inv >= 100000) $dailyRate = 4.0;
                elseif ($inv >= 25000) $dailyRate = 3.5;
                elseif ($inv >= 10000) $dailyRate = 3.0;
                elseif ($inv >= 5000) $dailyRate = 2.5;
                elseif ($inv >= 1000) $dailyRate = 2.0;
                elseif ($inv >= 100) $dailyRate = 1.5;

                $dailyIncome = ($inv * $dailyRate) / 100;
                $monthlyIncome = $dailyIncome * 30;
                $cap3x = $inv * 3;
                $cap5x = $inv * 5;

                if ($isEn) {
                    return "### 📊 ROI & Earnings Breakdown for **$".number_format($inv, 2)."**\n\n".
                        "Hello! Here is the clear return structure for your **$".number_format($inv, 2)."** package based on our official business plan:\n\n".
                        "- **Daily ROI Rate**: **{$dailyRate}% per day**.\n".
                        "- **Daily Profit**: **$".number_format($dailyIncome, 2)." / day** credited directly to your wallet.\n".
                        "- **Estimated Monthly Return (30 Days)**: **$".number_format($monthlyIncome, 2)."** (approx. ".( $dailyRate * 30 )."% monthly yield).\n".
                        "- **Basic 3X Earning Limit**: **$".number_format($cap3x, 2)."** (without direct referrals).\n".
                        "- **Booster 5X Earning Limit**: **$".number_format($cap5x, 2)."** (unlocked as soon as you have 1 active direct referral!).\n\n".
                        "You can withdraw your earnings anytime 24/7 or refer team members to hit your 5X booster faster.";
                }

                return "### 📊 **$".number_format($inv, 2)."** Package Par Aapki Earning\n\n".
                    "Aapke **$".number_format($inv, 2)."** package ke anusaar platform se aapko yeh verified returns milte hain:\n\n".
                    "- **Daily Arbitrage Rate**: **{$dailyRate}% per day**.\n".
                    "- **Daily Income**: **$".number_format($dailyIncome, 2)." / din** seedha aapke wallet mein.\n".
                    "- **Monthly Return (30 Din)**: **$".number_format($monthlyIncome, 2)."** (lagbhag ".( $dailyRate * 30 )."% monthly yield).\n".
                    "- **Basic 3X Capping Limit**: **$".number_format($cap3x, 2)."** (bina kisi direct referral ke).\n".
                    "- **Booster 5X Capping Limit**: **$".number_format($cap5x, 2)."** (sirf 1 active direct refer karte hi unlock ho jati hai!).\n\n".
                    "Aap is return ko daily withdraw kar sakte hain ya team build karke 5X potential tezi se achieve kar sakte hain.";

            case 'roi_tiers':
                if ($isEn) {
                    return "### ⚡ Quantum ARB Packages & Daily ROI Yields\n\n".
                        "Our algorithmic arbitrage model provides 6 structured investment packages starting from just $100:\n\n".
                        "1. **Starter Tier ($100 – $999)**: **1.5% Daily ROI** (approx. 45% Monthly)\n".
                        "2. **Standard Tier ($1,000 – $4,999)**: **2.0% Daily ROI** (approx. 60% Monthly)\n".
                        "3. **Advance Tier ($5,000 – $9,999)**: **2.5% Daily ROI** (approx. 75% Monthly)\n".
                        "4. **Elite Tier ($10,000 – $24,999)**: **3.0% Daily ROI** (approx. 90% Monthly)\n".
                        "5. **Master Tier ($25,000 – $99,999)**: **3.5% Daily ROI** (approx. 105% Monthly)\n".
                        "6. **VIP Whale Tier ($100,000+)**: **4.0% Daily ROI** (approx. 120% Monthly)\n\n".
                        "Profits accrue every 24 hours in USDT and can be withdrawn or compounded anytime.";
                }

                return "### ⚡ Quantum ARB Packages & Daily ROI Tiers\n\n".
                    "Platform par **6 structured packages** hain jo daily automated arbitrage return dete hain:\n\n".
                    "1. **Starter ($100 – $999)**: **1.5% Daily ROI** (lagbhag 45% Monthly)\n".
                    "2. **Standard ($1,000 – $4,999)**: **2.0% Daily ROI** (lagbhag 60% Monthly)\n".
                    "3. **Advance ($5,000 – $9,999)**: **2.5% Daily ROI** (lagbhag 75% Monthly)\n".
                    "4. **Elite ($10,000 – $24,999)**: **3.0% Daily ROI** (lagbhag 90% Monthly)\n".
                    "5. **Master ($25,000 – $99,999)**: **3.5% Daily ROI** (lagbhag 105% Monthly)\n".
                    "6. **VIP Whale ($100,000+)**: **4.0% Daily ROI** (lagbhag 120% Monthly)\n\n".
                    "Yeh returns har 24 ghante mein aapke wallet mein credit hote hain.";

            case 'direct_income':
                if ($isEn) {
                    return "### 🎯 Direct Referral Commission (5% Instant)\n\n".
                        "- When you invite a member using your personal referral link, you receive a flat **5% instant direct bonus** on their package amount.\n".
                        "- Example: If your direct member joins with **$2,000**, you immediately receive **$100 (5%)** in your wallet.\n".
                        "- Unlimited direct referrals are allowed.\n".
                        "- Just 1 active direct referral upgrades your earning capping limit from 3X to 5X!";
                }

                return "### 🎯 Direct Referral Income (5% Instant Commission)\n\n".
                    "Jab bhi aap kisi member ko direct sponsor karte hain:\n\n".
                    "- Aapko unke package ka **flat 5% instant Direct Commission** milta hai.\n".
                    "- Example: Agar aapke direct refer member ne **$2,000** ka package lagaya, to turant aapko **$100 (5%)** wallet mein mil jayega.\n".
                    "- Direct referral ki koi limit nahi hai, aap unlimited members jod sakte hain.\n".
                    "- Sirf 1 active direct referral jodne par aapki **Capping 3X se badhkar 5X** ho jati hai!";

            case 'level_income':
                if ($isEn) {
                    return "### 🌐 20-Level Deep ROI-to-ROI Matching Generation Income\n\n".
                        "Earn daily recurring matching bonuses based on the daily arbitrage yield of your entire team across 20 generation levels:\n\n".
                        "- **Level 1**: **15%** of daily ROI earned by direct members\n".
                        "- **Level 2**: **10%** of daily ROI\n".
                        "- **Levels 3 – 5**: **5%** of daily ROI\n".
                        "- **Levels 6 – 10**: **3%** of daily ROI\n".
                        "- **Levels 11 – 20**: **2%** of daily ROI\n\n".
                        "This income is paid out daily for powerful recurring passive growth.";
                }

                return "### 🌐 20-Level ROI-to-ROI Generation Income\n\n".
                    "Quantum ARB ka sabse bada earning stream **20-Level ROI Matching** hai:\n\n".
                    "- **Level 1**: **15%** of daily ROI earned by direct members\n".
                    "- **Level 2**: **10%** of daily ROI\n".
                    "- **Levels 3 – 5**: **5%** of daily ROI\n".
                    "- **Levels 6 – 10**: **3%** of daily ROI\n".
                    "- **Levels 11 – 20**: **2%** of daily ROI\n\n".
                    "Yeh matching income team ke har daily profit ke sath aapko daily aati hai.";

            case 'capping':
                if ($isEn) {
                    return "### 🛡️ 3X & 5X Safety Capping Multiplier Rules\n\n".
                        "Our platform operates with transparent mathematical capping to protect all participants:\n\n".
                        "1. **Basic 3X Capping (300%)**:\n".
                        "   - If you have no active direct referrals (downline = 0), your total maximum earnings cannot exceed 3X (300%) of your active package.\n".
                        "2. **Booster 5X Capping (500%)**:\n".
                        "   - As soon as you sponsor **1 active direct referral**, your capping ceiling boosts to **5X (500%)**!\n".
                        "3. **Re-topup / Cycle Renewal**:\n".
                        "   - Once you hit 3X or 5X capping, you can re-topup your package to trigger a brand-new 5X cycle.";
                }

                return "### 🛡️ 3X & 5X Safety Capping Booster Rules\n\n".
                    "Platform ki long-term stability aur security ke liye capping mechanism banaya gaya hai:\n\n".
                    "1. **3X Basic Capping (300%)**:\n".
                    "   - Agar aapka koi active direct referral nahi hai, to aap apne package ka maximum **3X (300%)** earn kar sakte hain.\n".
                    "2. **5X Booster Capping (500%)**:\n".
                    "   - Jaise hi aap **1 Active Direct Referral** complete karte hain, aapki limit boost hokar **5X (500%)** ho jati hai!\n".
                    "3. **Re-topup**:\n".
                    "   - Capping reach hone par aap re-topup karke fresh 5X cycle chalu kar sakte hain.";

            case 'ranks':
                if ($isEn) {
                    return "### 🏆 Progressive Leadership Ranks & Special Trading Bonus\n\n".
                        "Top network builders unlock additional Special Trading team bonuses:\n\n".
                        "- **Silver Rank**: **15%** Special Trading Team Bonus\n".
                        "- **Gold Rank**: **30%** Special Trading Team Bonus\n".
                        "- **Diamond Rank**: **40%** Special Trading Team Bonus\n\n".
                        "This bonus rewards dedicated leadership volume on top of standard generation yields.";
                }

                return "### 🏆 Progressive Rank System & Special Trading Bonus\n\n".
                    "Platform ke top leaders ke liye additional **Special Trading Bonus** ranks hain:\n\n".
                    "- **Silver Rank**: **15%** Special Trading Team Bonus\n".
                    "- **Gold Rank**: **30%** Special Trading Team Bonus\n".
                    "- **Diamond Rank**: **40%** Special Trading Team Bonus\n\n".
                    "Yeh reward aapke standard 20-level income ke alawa diya jata hai.";

            case 'deposit_withdrawal':
                if ($isEn) {
                    return "### 💳 Deposit, Activation & Withdrawal Details\n\n".
                        "- **Minimum Activation**: $100 USDT.\n".
                        "- **Accepted Currencies**: USDT (BEP-20 / TRC-20) with automated instant confirmations.\n".
                        "- **Wallets**: Cash Wallet (withdrawable payouts), P2P Wallet (instant transfers), and Staking Wallet.\n".
                        "- **Withdrawals**: Automated 24/7 processing directly to your verified on-chain address.";
                }

                return "### 💳 Deposit, Activation & Withdrawal Rules\n\n".
                    "- **Minimum Activation**: Sirf **$100 USDT** se aap start kar sakte hain.\n".
                    "- **Accepted Currencies**: **USDT (BEP-20 / TRC-20)** fast verification ke sath.\n".
                    "- **Wallets**: Cash Wallet (direct payout), P2P Wallet (member transfers), aur Staking Wallet.\n".
                    "- **Withdrawal**: Automated processing 24/7 seedha aapke crypto address par hoti hai.";

            default:
            case 'overview':
                if ($isEn) {
                    return "### 🚀 Quantum ARB Business Plan Overview\n\n".
                        "Quantum ARB is a transparent, automated algorithmic arbitrage platform offering 5 lucrative income streams:\n\n".
                        "1. **Daily Arbitrage ROI (1.5% – 4.0% Daily)**: Guaranteed daily yields across 6 package tiers ($100 to $100K+).\n".
                        "2. **Direct Referral Bonus (5%)**: 5% instant cash bonus on every direct sponsored member.\n".
                        "3. **20-Level ROI Matching**: 15% on L1, 10% on L2, 5% on L3-5, 3% on L6-10, 2% on L11-20 based on downline ROI.\n".
                        "4. **3X / 5X Capping Multiplier**: 3X basic cap, easily upgraded to 5X with just 1 active direct referral.\n".
                        "5. **Leadership Ranks**: Up to 40% special trading bonus for Silver, Gold, and Diamond ranks.\n\n".
                        "Feel free to speak to me or type your questions to calculate exact returns for any package amount!";
                }

                return "### 🚀 Quantum ARB Complete Business Plan Explainer\n\n".
                    "Quantum ARB ek transparent, automated arbitrage ecosystem hai jisme **5 Powerful Income Streams** hain:\n\n".
                    "1. **Daily Arbitrage ROI (1.5% – 4.0% Daily)**: $100 se $100,000+ packages par guaranteed daily returns.\n".
                    "2. **Direct Referral Bonus (5%)**: Har direct member activation par 5% instant payout.\n".
                    "3. **20-Level ROI Matching**: Team ke daily ROI ka 15% (L1), 10% (L2), 5% (L3-5), 3% (L6-10), 2% (L11-20).\n".
                    "4. **3X / 5X Capping Multiplier**: 1 direct refer karke apni capping ko 3X se 5X boost karein.\n".
                    "5. **Leadership Ranks**: Silver (15%), Gold (30%), Diamond (40%) special trading rewards.\n\n".
                    "Aap mujhse mic par Hindi ya English mein poochh sakte hain ya kisi bhi package ka return calculate karwa sakte hain!";
        }
    }
}
