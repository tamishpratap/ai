<?php

namespace App\Http\Controllers;

use App\Services\AI\IraNavigationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IraNavigationController extends Controller
{
    /**
     * Resolve natural user speech into a validated, context-aware navigation intent.
     * Server-authoritative role verification prevents cross-role authorization bypass.
     */
    public function resolve(Request $request, IraNavigationService $navService): JsonResponse
    {
        $speech = trim((string) $request->input('speech', $request->input('message', '')));
        $currentPage = (string) $request->input('current_page', '');
        $currentMode = (string) $request->input('current_mode', 'chat');
        $context = (array) $request->input('context', []);

        // Authoritatively determine server-side role from session and route context.
        // Never trust client-provided role or destination parameters.
        $role = $this->resolveServerRole($request, $currentPage);

        try {
            $result = $navService->resolveIntent($speech, $role, $currentPage, $currentMode, $context);
            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('[IraNavigationController] Resolution exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'intent' => 'unsupported_action',
                'destination_key' => null,
                'target_url' => null,
                'speech' => 'Kripya thodi der baad punah prayas karein.',
                'response_language' => 'hinglish',
                'confidence' => 0.0,
                'needs_clarification' => false,
                'context' => [],
            ], 500);
        }
    }

    /**
     * Authoritatively resolve user role based on Laravel session authentication and route context.
     *
     * In AI Demo:
     * - AdminAuth middleware checks: $request->session()->has('ADMIN_LOGIN')
     * - MemberAuth middleware checks: $request->session()->has('MEMBER_ID')
     *
     * If both session keys exist (e.g. admin testing member pages or admin impersonating a member),
     * the current active page context determines the operative panel:
     * - If browsing /admin/* and has ADMIN_LOGIN -> admin
     * - If browsing /member/* and has MEMBER_ID -> member
     * - Otherwise falls back to unambiguous single-session check or guest.
     */
    protected function resolveServerRole(Request $request, string $currentPage): string
    {
        $hasAdminSession = $request->session()->has('ADMIN_LOGIN') || $request->session()->has('ADMIN_ID');
        $hasMemberSession = $request->session()->has('MEMBER_ID') || $request->session()->has('MEMBER_LOGIN');

        $path = parse_url($currentPage, PHP_URL_PATH) ?? $currentPage;
        $isAdminPath = str_starts_with($path, '/admin') || str_starts_with($path, 'admin');
        $isMemberPath = str_starts_with($path, '/member') || str_starts_with($path, 'member');

        // Disambiguate when both session cookies are present
        if ($hasAdminSession && $hasMemberSession) {
            if ($isMemberPath) {
                return 'member';
            }
            if ($isAdminPath) {
                return 'admin';
            }
            // If on a public page with dual login, prefer member for safety
            return 'member';
        }

        // Single session evaluation
        if ($hasAdminSession) {
            return 'admin';
        }

        if ($hasMemberSession) {
            return 'member';
        }

        return 'guest';
    }
}
