<?php

namespace App\Http\Controllers;

use App\Services\AI\IraFormRegistry;
use App\Services\AI\IraFormUnderstandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IraFormController extends Controller
{
    /**
     * Handle voice form filling step via POST /ira/form-step
     *
     * Validates user role authoritatively from session,
     * verifies form key against IraFormRegistry,
     * extracts and normalizes field updates, and returns safe update payload.
     * Never executes business form submissions or mutations.
     */
    public function formStep(
        Request $request,
        IraFormRegistry $registry,
        IraFormUnderstandingService $formAi
    ): JsonResponse {
        $action = trim((string) $request->input('action', 'turn'));
        $speech = trim((string) $request->input('speech', ''));
        $formKey = trim((string) $request->input('form_key', ''));
        $currentPage = (string) $request->input('current_page', '');
        $currentFields = (array) $request->input('current_fields', []);
        $guideState = (array) $request->input('guide_state', []);
        $context = (array) $request->input('context', []);
        $language = trim((string) $request->input('language', ''));
        if ($language !== '') {
            $context['language'] = $language;
        }

        $domEligibleFields = (array) $request->input('dom_eligible_fields', []);
        if (!empty($domEligibleFields)) {
            $context['dom_eligible_fields'] = $domEligibleFields;
        }

        // Authoritatively determine role from server session
        $role = $this->resolveServerRole($request, $currentPage);

        if ($formKey === '') {
            return response()->json([
                'success' => false,
                'status' => 'missing_form_key',
                'speech' => 'Form key missing. Kripya form select karein.',
                'form_updates' => [],
            ], 400);
        }

        $schema = $registry::getForm($formKey);
        if (!$schema) {
            return response()->json([
                'success' => false,
                'status' => 'unknown_form',
                'speech' => 'Yeh form system mein registered nahi hai.',
                'form_updates' => [],
            ], 404);
        }

        if (!$registry::isFormAuthorized($formKey, $role)) {
            Log::warning('[IraFormController] Unauthorized form access attempt', [
                'form_key' => $formKey,
                'role' => $role,
                'current_page' => $currentPage,
            ]);

            return response()->json([
                'success' => false,
                'status' => 'unauthorized',
                'speech' => 'Aapko is form ko access karne ki anumati nahi hai.',
                'form_updates' => [],
            ], 403);
        }

        try {
            if ($action === 'init') {
                $result = $formAi->initGuidedSession($formKey, $role, $currentFields, $language ?: 'hi', $context);
            } elseif (!empty($guideState)) {
                $result = $formAi->processGuidedTurn($speech, $formKey, $role, $currentFields, $guideState, $context);
            } else {
                $result = $formAi->processFormTurn($speech, $formKey, $role, $currentFields, $context);
            }
            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('[IraFormController] Form turn exception', [
                'form_key' => $formKey,
                'action' => $action,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'speech' => 'Form process karne mein dikkat aayi. Kripya punah prayas karein.',
                'form_updates' => [],
            ], 500);
        }
    }

    /**
     * Discover registered forms on the current page path.
     * Route: GET /ira/forms-for-page?path=/member/staking
     */
    public function formsForPage(Request $request, IraFormRegistry $registry): JsonResponse
    {
        $path = (string) $request->input('path', '');
        $role = $this->resolveServerRole($request, $path);

        $matched = $registry::findFormsByPath($path, $role);

        // Sanitize schemas for frontend consumption (remove sensitive metadata)
        $sanitized = [];
        foreach ($matched as $k => $f) {
            $fields = [];
            foreach ($f['fields'] as $fk => $fd) {
                $fields[$fk] = [
                    'name' => $fd['name'],
                    'selector' => $fd['selector'],
                    'label' => $fd['label'],
                    'type' => $fd['type'],
                    'required' => $fd['required'] ?? false,
                    'sensitive' => $fd['sensitive'] ?? false,
                    'read_only' => $fd['read_only'] ?? false,
                    'file_upload' => $fd['file_upload'] ?? false,
                    'options' => $fd['options'] ?? [],
                ];
            }

            $sanitized[$k] = [
                'key' => $f['key'],
                'title' => $f['title'],
                'purpose' => $f['purpose'],
                'selector' => $f['selector'],
                'risk' => $f['risk'],
                'fields' => $fields,
            ];
        }

        return response()->json([
            'success' => true,
            'role' => $role,
            'path' => $path,
            'forms' => $sanitized,
        ]);
    }

    /**
     * Authoritatively resolve user role based on Laravel session authentication and route context.
     */
    protected function resolveServerRole(Request $request, string $currentPage): string
    {
        $hasAdminSession = $request->session()->has('ADMIN_LOGIN') || $request->session()->has('ADMIN_ID');
        $hasMemberSession = $request->session()->has('MEMBER_ID') || $request->session()->has('MEMBER_LOGIN');

        $path = parse_url($currentPage, PHP_URL_PATH) ?? $currentPage;
        $isAdminPath = str_starts_with($path, '/admin') || str_starts_with($path, 'admin');
        $isMemberPath = str_starts_with($path, '/member') || str_starts_with($path, 'member');

        if ($hasAdminSession && $hasMemberSession) {
            if ($isMemberPath) {
                return 'member';
            }
            if ($isAdminPath) {
                return 'admin';
            }
            return 'member';
        }

        if ($hasAdminSession) {
            return 'admin';
        }

        if ($hasMemberSession) {
            return 'member';
        }

        return 'guest';
    }
}
