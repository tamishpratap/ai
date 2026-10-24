<?php

namespace App\Http\Controllers\ChatAi;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\ChatAi\AdminChatReportService;
use App\Services\ChatAi\ChatGeminiBridge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAiChatController extends Controller
{
    public function __construct(
        protected AdminChatReportService $reportService,
        protected ChatGeminiBridge $geminiBridge
    ) {}

    /**
     * Display the Admin AI Chat page.
     */
    public function index()
    {
        $hasAdminLogin = session('ADMIN_LOGIN');
        $adminId = session('ADMIN_ID');

        if (!$hasAdminLogin || empty($adminId)) {
            return redirect('/admin');
        }

        $admin = Admin::find($adminId);
        if (!$admin) {
            return redirect('/admin');
        }

        return view('admin.ai.chat', ['admin' => $admin]);
    }

    /**
     * Handle AJAX chat message from authenticated administrator.
     */
    public function chat(Request $request): JsonResponse
    {
        // 1. Validate payload (supports both 'message' and 'prompt' keys)
        $request->validate([
            'message' => 'required_without:prompt|nullable|string|max:1000',
            'prompt' => 'required_without:message|nullable|string|max:1000',
        ]);

        // 2. Validate session credentials
        $adminId = session('ADMIN_ID');
        $hasAdminLogin = session('ADMIN_LOGIN');
        if (!$hasAdminLogin || empty($adminId)) {
            return response()->json([
                'success' => false,
                'answer' => 'Session expired. Please log in again.',
            ], 401);
        }

        $admin = Admin::find($adminId);
        if (!$admin) {
            return response()->json([
                'success' => false,
                'answer' => 'Session expired. Please log in again.',
            ], 401);
        }

        $query = trim((string) ($request->post('message') ?? $request->post('prompt') ?? ''));
        if ($query === '') {
            return response()->json([
                'message' => 'The message or prompt field cannot be empty.',
                'errors' => ['message' => ['The message or prompt field cannot be empty.']]
            ], 422);
        }

        $lowercaseQuery = strtolower($query);

        // 3. Security Filter: Block injection attempts
        $blockedKeywords = ['database dump', 'drop table', 'union select', 'passwords', 'secret key'];
        foreach ($blockedKeywords as $word) {
            if (str_contains($lowercaseQuery, $word)) {
                return response()->json([
                    'success' => true,
                    'answer' => 'This AI assistant can only provide safe read-only project and database information allowed for the logged-in admin.',
                    'reply' => 'This AI assistant can only provide safe read-only project and database information allowed for the logged-in admin.',
                    'source' => 'security_shield',
                    'context' => ['intent' => 'blocked_security'],
                ]);
            }
        }

        // 4. Candidate Member Resolution
        $member = $this->reportService->resolveMemberCandidate($query);
        if (!$member && $this->reportService->hasExplicitMemberPattern($query)) {
            // An explicit member ID pattern was typed, but no matching record exists in DB
            return response()->json([
                'success' => true,
                'answer' => 'No member was found with this member ID.',
                'reply' => 'No member was found with this member ID.',
                'source' => 'fallback',
                'context' => [
                    'intent' => 'member_lookup_not_found',
                    'target_member' => null,
                ],
            ]);
        }

        // 5. Detect Scope & Intent
        $intent = $this->reportService->detectScopeAndIntent($query, $member);

        // 6. Compile Verified Read-Only Context
        $context = $this->reportService->compileContext($intent, $member);

        // 7. Connect to Gemini or Trigger Fallback
        $systemInstruction = 'You are a read-only database query assistant for admin panel. '
            . 'Answer ONLY using this real database context: ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '. '
            . 'Keep responses clear, professional, and matching the language of admin query. Never invent facts, data, or execute mutations.';

        $source = 'gemini';
        $answer = $this->geminiBridge->generateAnswer($systemInstruction, 'Admin query: ' . $query);

        if (!$answer) {
            $source = 'fallback';
            $answer = $this->reportService->buildFallbackResponse($intent, $context);
        }

        return response()->json([
            'success' => true,
            'answer' => $answer,
            'reply' => $answer,
            'source' => $source,
            'context' => [
                'intent' => $intent,
                'target_member' => $member ? $member->memberid : null,
            ],
        ]);
    }
}
