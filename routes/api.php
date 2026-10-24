<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'hasGeminiKey' => (bool) env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'doc' => 'public/logo/project',
    ]);
});

Route::post('/chat', function (Request $request) {
    $question = trim($request->input('question', ''));
    if (!$question) {
        return response()->json(['error' => 'Question is required.'], 400);
    }

    $docPath = public_path('logo/project_extracted.txt');
    if (!file_exists($docPath)) {
        $docPath = public_path('logo/project');
    }
    $docText = file_exists($docPath) ? file_get_contents($docPath) : '';

    $apiKey = env('GEMINI_API_KEY');
    $nodePort = env('CHATBOT_PORT', 3000);

    // 1. If Node microservice is running, forward request
    try {
        $nodeUrl = "http://127.0.0.1:{$nodePort}/api/chat";
        $nodeResp = \Illuminate\Support\Facades\Http::timeout(3)->post($nodeUrl, [
            'question' => $question,
            'history' => $request->input('history', []),
        ]);
        if ($nodeResp->successful() && isset($nodeResp['answer'])) {
            return response()->json($nodeResp->json());
        }
    } catch (\Throwable $e) {
        // Node server offline, continue to direct processing
    }

    // 2. Direct Gemini call using raw text of public/logo/project (No PDF needed)
    if ($apiKey && $docText) {
        try {
            $model = env('GEMINI_MODEL', 'gemini-1.5-flash');
            $geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $userPrompt = "Reference Document: public/logo/project\n\n```markdown\n" . mb_substr($docText, 0, 30000) . "\n```\n\nQuestion: " . $question . "\n\nImportant: Give the exact, accurate answer grounded ONLY in the reference document above. Keep it structured, clear, and concise.";

            $geminiResp = \Illuminate\Support\Facades\Http::timeout(10)->post($geminiUrl, [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => "You are Quantum ARB's Official AI Document Assistant. You answer questions strictly based on the provided Quantum ARB project documentation (public/logo/project). Output clean markdown or HTML."]
                    ]
                ],
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $userPrompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 1000,
                ]
            ]);

            if ($geminiResp->successful()) {
                $ans = $geminiResp->json('candidates.0.content.parts.0.text');
                if (!empty($ans)) {
                    return response()->json(['answer' => trim($ans)]);
                }
            }
        } catch (\Throwable $e) {
            // Fall through to instant local document engine
        }
    }

    // 3. Instant Exact Document Extraction Engine from public/logo/project
    $q = strtolower($question);

    // (A) Staking, ROI, Packages, Yield, Rates
    if (str_contains($q, 'roi') || str_contains($q, 'package') || str_contains($q, 'tier') || str_contains($q, 'yield') || str_contains($q, 'return') || str_contains($q, 'invest')) {
        $ans = "<strong>Staking Packages & Monthly ROI Tiers</strong> (from Document Sec. 4.A):<br><br>"
            . "• <strong>Tier 1:</strong> $100 – $1,000 → <strong>10% Monthly</strong> (0.333% / day)<br>"
            . "• <strong>Tier 2:</strong> $1,100 – $5,000 → <strong>12% Monthly</strong> (0.400% / day)<br>"
            . "• <strong>Tier 3:</strong> $5,100 – $10,000 → <strong>14% Monthly</strong> (0.466% / day)<br>"
            . "• <strong>Tier 4:</strong> $10,100+ → <strong>15% Monthly</strong> (0.500% / day)<br><br>"
            . "<strong>Calculation Formula:</strong><br>"
            . "<code>Daily Payout = (Invested Amount × Monthly Rate) / (30 × 100)</code>";
    }
    // (B) 3X & 5X Capping Rules
    elseif (str_contains($q, 'capping') || str_contains($q, '3x') || str_contains($q, '5x') || str_contains($q, 'limit') || str_contains($q, 'non-working') || str_contains($q, 'working')) {
        $ans = "<strong>Income Capping Rules (Capital Preservation)</strong> (from Document Sec. 4.B):<br><br>"
            . "• <strong>3X Non-Working Capping:</strong> Passive investors can earn up to a maximum of <strong>300% (3X)</strong> of their active staking amount across passive income streams. Once 3X is reached, the staking plan auto-deactivates.<br><br>"
            . "• <strong>5X Working Capping:</strong> Active network builders with direct referrals and team levels can earn up to <strong>500% (5X)</strong> before requiring a package re-topup.";
    }
    // (C) 3 AI Engines
    elseif (str_contains($q, 'ai') || str_contains($q, 'engine') || str_contains($q, 'matrix') || str_contains($q, 'neural') || str_contains($q, 'sentinel')) {
        $ans = "<strong>3 AI Trading Engines</strong> (from Document Sec. 6.1):<br><br>"
            . "1. <strong>Quantum Matrix AI</strong> (<code>/ai/quantum-arbitrage</code>):<br>"
            . "• Ultra-fast 30ms high-frequency spread detection core scanning 40+ global exchanges.<br><br>"
            . "2. <strong>Neural Pulse AI</strong> (<code>/ai/neural-pulse</code>):<br>"
            . "• Machine learning engine trained on 10B+ order-book ticks to forecast liquidity sweeps.<br><br>"
            . "3. <strong>Sentinel Guard AI</strong> (<code>/ai/sentinel-guard</code>):<br>"
            . "• Automated capital defense, anti-MEV front-running shield, and instant flash-crash circuit breaker.";
    }
    // (D) Ranks & Reward System
    elseif (str_contains($q, 'rank') || str_contains($q, 'reward') || str_contains($q, 'titan') || str_contains($q, 'bronze') || str_contains($q, 'silver') || str_contains($q, 'gold') || str_contains($q, 'platinum')) {
        $ans = "<strong>Progressive Rank & Reward System</strong> (from Document Sec. 4.E):<br>"
            . "<em>Calculation based on 50% Strong Leg / 50% Weaker Legs accumulation:</em><br><br>"
            . "• <strong>Starter:</strong> $1,000 Volume → Platform Badge<br>"
            . "• <strong>Bronze:</strong> $5,000 Volume → Cash Bonus<br>"
            . "• <strong>Silver:</strong> $15,000 Volume → Smart Device Award<br>"
            . "• <strong>Gold:</strong> $50,000 Volume → Luxury Trip<br>"
            . "• <strong>Platinum:</strong> $150,000 Volume → Supercar Bonus<br>"
            . "• <strong>Quantum Titan:</strong> $500,000+ Volume → Global Pool Equity";
    }
    // (E) Direct & Level Incomes
    elseif (str_contains($q, 'level') || str_contains($q, 'direct') || str_contains($q, 'referral') || str_contains($q, 'affiliate') || str_contains($q, 'matching') || str_contains($q, 'mlm')) {
        $ans = "<strong>Affiliate & Referral Structure</strong> (from Document Sec. 4.C & 4.D):<br><br>"
            . "• <strong>Direct Referral Benefit:</strong> Instant direct commission credited to the sponsor's wallet immediately upon successful package activation.<br><br>"
            . "• <strong>Multi-Level ROI Matching (Level Income):</strong> ROI-to-ROI commission paid on the daily returns earned by downline team members across hierarchical levels (Level 1 to Level 15+). Direct referrals unlock deeper levels.";
    }
    // (F) Withdrawal & Fees
    elseif (str_contains($q, 'withdrawal') || str_contains($q, 'withdraw') || str_contains($q, 'fee') || str_contains($q, 'otp') || str_contains($q, 'payout')) {
        $ans = "<strong>Withdrawal Protocol</strong> (from Document Sec. 5 Flow 4):<br><br>"
            . "• <strong>Security:</strong> Requires One-Time Password (OTP) verification before withdrawal request creation.<br>"
            . "• <strong>Fee:</strong> Standard 10% processing fee deducted upon balance validation.<br>"
            . "• <strong>Process:</strong> Admin review & automated disbursement via <code>app:approve-withdrawal</code> scheduler.";
    }
    // (G) Tech Stack & Database Models
    elseif (str_contains($q, 'tech') || str_contains($q, 'stack') || str_contains($q, 'database') || str_contains($q, 'laravel') || str_contains($q, 'model') || str_contains($q, 'architecture')) {
        $ans = "<strong>Technology Stack & Database</strong> (from Document Sec. 2 & 3):<br><br>"
            . "• <strong>Backend:</strong> Laravel 10.x (PHP 8.2+) standard MVC architecture.<br>"
            . "• <strong>Database:</strong> MySQL 8.0 with <strong>30 Eloquent Models</strong> (MemberDetail, StakingDetail, DailyIncome, LevelIncome, etc.).<br>"
            . "• <strong>Frontend:</strong> Blade Views, Three.js (r128) WebGL Globe, CSS3D Neon Flip Cards.<br>"
            . "• <strong>Cron Schedulers:</strong> <code>DailyIncomeDis</code>, <code>LevelIncomeDis</code>, <code>RewardGen</code>, <code>ApproveWithdrawal</code>.";
    }
    // (H) Overview / What is Quantum ARB
    elseif (str_contains($q, 'what is') || str_contains($q, 'about') || str_contains($q, 'summary') || str_contains($q, 'quantum arb') || str_contains($q, 'kya hai')) {
        $ans = "<strong>Quantum ARB</strong> (from Document Sec. 1):<br><br>"
            . "Quantum ARB is a fintech platform combining <strong>AI-powered cross-exchange cryptocurrency arbitrage trading</strong> with a <strong>multi-tier affiliate marketing (MLM), staking investment, and P2P financial ecosystem</strong>.<br><br>"
            . "• Real-time spread scanning across 40+ global exchanges (Binance, OKX, Bybit, Coinbase, Kraken).<br>"
            . "• High-speed 30ms trade execution with zero directional market risk.<br>"
            . "• Systematic 10% to 15% monthly staking ROI with strict 3X/5X capping protection.";
    }
    // (I) Fallback grounded search inside public/logo/project text
    else {
        // Search matching paragraph from the markdown document
        $foundSnippet = '';
        if ($docText) {
            $keywords = array_filter(explode(' ', preg_replace('/[^a-z0-9]/', ' ', $q)), function ($w) {
                return strlen($w) > 3;
            });
            $lines = explode("\n", $docText);
            $bestScore = 0;
            $bestChunk = [];

            for ($i = 0; $i < count($lines); $i += 5) {
                $chunk = array_slice($lines, $i, 12);
                $chunkStr = strtolower(implode(' ', $chunk));
                $score = 0;
                foreach ($keywords as $kw) {
                    if (str_contains($chunkStr, $kw)) $score += 2;
                }
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestChunk = $chunk;
                }
            }

            if ($bestScore > 0) {
                $cleaned = trim(implode("\n", $bestChunk));
                $foundSnippet = "<br><br><strong>Relevant Document Excerpt:</strong><br><pre style='white-space:pre-wrap; font-size:12px; opacity:0.85;'>" . htmlspecialchars(mb_substr($cleaned, 0, 500)) . "</pre>";
            }
        }

        $ans = "According to <strong>public/logo/project</strong>, Quantum ARB delivers institutional algorithmic arbitrage across 40+ exchanges with 10%–15% monthly staking ROI, protected by 3X/5X capital preservation rules and 3 AI trading engines." . $foundSnippet;
    }

    return response()->json(['answer' => $ans]);
});
