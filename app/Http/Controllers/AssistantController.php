<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssistantChatRequest;
use App\Http\Requests\AssistantLiveSessionRequest;
use App\Http\Requests\AssistantLiveTurnRequest;
use App\Http\Requests\AssistantPdfRequest;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiUsageLog;
use App\Services\Gemini\GeminiConversationService;
use App\Services\Gemini\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AssistantController extends Controller
{
    public function __construct(
        private readonly GeminiService $gemini,
        private readonly GeminiConversationService $conversations,
    ) {}

    public function createSession(AssistantLiveSessionRequest $request): JsonResponse
    {
        $memberId = $this->memberId();
        $conversation = ($request->filled('conversation_id'))
            ? $this->conversations->owned($request->string('conversation_id'), $memberId)
            : $this->conversations->latestOrCreate($memberId, 'voice');
        $apiKey = $this->extractApiKey($request);

        try {
            $session = $this->gemini->createLiveToken($apiKey);

            return $this->success('JARVIS voice session ready.', [
                'conversation' => $conversation,
                'live' => $session,
                'history' => $this->conversations->context($conversation, 20),
                'messages' => $this->conversations->rawMessages($conversation, 20),
            ], 201);
        } catch (RuntimeException $exception) {
            $this->conversations->logUsage($conversation, null, 'live_session', 'voice', 'voice', [], 'failed', $exception::class);

            return $this->error($exception->getMessage(), 503);
        }
    }

    public function chat(AssistantChatRequest $request): JsonResponse
    {
        $memberId = $this->memberId();
        $conversation = ($request->filled('conversation_id'))
            ? $this->conversations->owned($request->string('conversation_id'), $memberId)
            : $this->conversations->latestOrCreate($memberId, 'text');
        $prompt = $request->filled('quick_action')
            ? config('gemini.quick_actions.'.$request->string('quick_action'))
            : trim($request->string('message'));
        $requestType = $request->filled('quick_action') ? 'quick_action' : 'text_chat';
        $apiKey = $this->extractApiKey($request);

        return DB::transaction(function () use ($conversation, $prompt, $requestType, $request, $apiKey) {
            $userMessage = $this->conversations->addMessage($conversation, 'user', $prompt, 'text', 'text', [
                'quick_action' => $request->input('quick_action'),
            ]);

            try {
                $result = $this->gemini->generate($this->conversations->context($conversation), $apiKey);
                $assistantMessage = $this->conversations->addMessage(
                    $conversation, 'assistant', $result['text'], 'text', $request->boolean('speak') ? 'voice' : 'text'
                );
                $this->conversations->logUsage($conversation, $assistantMessage, $requestType, 'text', $request->boolean('speak') ? 'voice' : 'text', $result['usage']);

                return $this->success('Response generated.', [
                    'conversation' => $conversation->fresh(),
                    'user_message' => $userMessage,
                    'assistant_message' => $assistantMessage,
                ]);
            } catch (RuntimeException $exception) {
                $this->conversations->logUsage($conversation, $userMessage, $requestType, 'text', 'text', [], 'failed', $exception::class);

                return $this->error($exception->getMessage(), 503);
            }
        });
    }

    public function persistLiveTurn(AssistantLiveTurnRequest $request): JsonResponse
    {
        $memberId = $this->memberId();
        $conversation = $this->conversations->owned($request->string('conversation_id'), $memberId);
        $existing = AiMessage::where('conversation_id', $conversation->id)
            ->where('metadata->turn_id', $request->string('turn_id'))->exists();
        if ($existing) {
            return $this->success('Turn was already stored.', ['duplicate' => true]);
        }

        $assistantMessage = DB::transaction(function () use ($request, $conversation) {
            if ($request->filled('user_transcript')) {
                $this->conversations->addMessage($conversation, 'user', trim($request->string('user_transcript')), 'voice', 'text', [
                    'turn_id' => (string) $request->string('turn_id'),
                ]);
            }
            $assistant = null;
            if ($request->filled('assistant_transcript')) {
                $assistant = $this->conversations->addMessage($conversation, 'assistant', trim($request->string('assistant_transcript')), 'voice', 'voice', [
                    'turn_id' => (string) $request->string('turn_id'),
                ]);
            }
            $usage = $request->input('usage', []);
            $this->conversations->logUsage($conversation, $assistant, 'live_voice', 'voice', 'voice', [
                'input_tokens' => $usage['promptTokenCount'] ?? null,
                'output_tokens' => $usage['responseTokenCount'] ?? null,
                'total_tokens' => $usage['totalTokenCount'] ?? null,
                'duration_ms' => $request->integer('duration_ms') ?: null,
            ]);

            return $assistant;
        });

        return $this->success('Voice turn stored.', ['assistant_message' => $assistantMessage], 201);
    }

    public function summarizePdf(AssistantPdfRequest $request): JsonResponse
    {
        $memberId = $this->memberId();
        $conversation = ($request->filled('conversation_id'))
            ? $this->conversations->owned($request->string('conversation_id'), $memberId)
            : $this->conversations->latestOrCreate($memberId, 'text');
        $file = $request->file('document');
        $fileName = Str::uuid().'.pdf';
        $path = $file->storeAs('assistant_uploads/'.($memberId ?? 'guest'), $fileName, 'local');
        $prompt = trim($request->input('prompt', 'Summarize this PDF clearly. Include key points, conclusions, and important action items.'));
        $apiKey = $this->extractApiKey($request);

        try {
            $history = $this->conversations->context($conversation);
            $history[] = ['role' => 'user', 'parts' => [
                ['text' => $prompt],
                ['inlineData' => ['mimeType' => 'application/pdf', 'data' => base64_encode(Storage::disk('local')->get($path))]],
            ]];
            $userMessage = $this->conversations->addMessage($conversation, 'user', $prompt, 'file', 'text', [
                'document_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);
            $result = $this->gemini->generate($history, $apiKey);
            $assistantMessage = $this->conversations->addMessage($conversation, 'assistant', $result['text'], 'file', 'text');
            $this->conversations->logUsage($conversation, $assistantMessage, 'pdf_summary', 'file', 'text', $result['usage']);

            return $this->success('PDF summarized.', ['conversation' => $conversation->fresh(), 'user_message' => $userMessage, 'assistant_message' => $assistantMessage]);
        } catch (RuntimeException $exception) {
            $this->conversations->logUsage($conversation, null, 'pdf_summary', 'file', 'text', [], 'failed', $exception::class);

            return $this->error($exception->getMessage(), 503);
        }
    }

    public function verifyKey(Request $request): JsonResponse
    {
        $key = trim((string) $request->input('api_key', ''));
        if (empty($key)) {
            return $this->error('API key cannot be empty.', 422);
        }

        try {
            $response = Http::timeout(10)->get('https://generativelanguage.googleapis.com/v1beta/models?key='.urlencode($key));

            if ($response->successful()) {
                $models = collect($response->json('models', []))->pluck('name')->all();
                $count = count($models);

                // Detect available preferred model
                $activeModel = 'gemini-1.5-flash';
                if (collect($models)->contains(fn ($m) => str_contains($m, 'gemini-2.5-flash'))) {
                    $activeModel = 'gemini-2.5-flash';
                } elseif (collect($models)->contains(fn ($m) => str_contains($m, 'gemini-2.0-flash'))) {
                    $activeModel = 'gemini-2.0-flash';
                }

                return $this->success('Gemini API Key is valid and active!', [
                    'valid' => true,
                    'models_count' => $count,
                    'active_model' => $activeModel,
                ]);
            }

            $errorMsg = $response->json('error.message') ?? 'Invalid API key or access denied by Google Gemini.';
            return $this->error($errorMsg, 400);
        } catch (\Exception $e) {
            return $this->error('Unable to connect to Google Gemini verification endpoint: '.$e->getMessage(), 500);
        }
    }

    public function image(Request $request): JsonResponse
    {
        return $this->error('Image generation requires an external generation model configuration.', 501);
    }

    public function index(): JsonResponse
    {
        $memberId = $this->memberId();
        $conversations = AiConversation::where('member_id', $memberId)
            ->withCount('messages')
            ->orderByDesc('last_message_at')
            ->limit(20)
            ->get();

        return $this->success('Conversations loaded.', ['conversations' => $conversations]);
    }

    public function show(AiConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        return $this->success('Conversation loaded.', ['conversation' => $conversation]);
    }

    public function messages(AiConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);
        $messages = $conversation->messages()->orderBy('created_at')->get();

        return $this->success('Messages loaded.', ['messages' => $messages]);
    }

    public function destroy(AiConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);
        $conversation->delete();

        return $this->success('Conversation deleted.');
    }

    public function stats(): JsonResponse
    {
        $memberId = $this->memberId();
        $usage = AiUsageLog::where('member_id', $memberId);
        $usageCount = (clone $usage)->count();

        return $this->success('Stats loaded.', [
            'chats' => AiConversation::where('member_id', $memberId)->count(),
            'tasks' => (clone $usage)->whereIn('request_type', ['quick_action', 'pdf_summary'])->count(),
            'usage_minutes' => round(((clone $usage)->sum('duration_ms') ?: 0) / 60000, 1),
            'success_rate' => $usageCount ? round(((clone $usage)->where('status', 'success')->count() / $usageCount) * 100, 1) : 0,
        ]);
    }

    private function memberId(): ?int
    {
        $id = session('MEMBER_ID');
        if (! is_numeric($id) || (int) $id <= 0) {
            return null;
        }

        return \App\Models\MemberDetail::where('id', (int) $id)->exists() ? (int) $id : null;
    }

    private function authorizeConversation(AiConversation $conversation): void
    {
        $memberId = $this->memberId();
        if ($memberId === null) {
            abort(401, 'Unauthenticated.');
        }

        if ((int) $conversation->member_id !== $memberId) {
            abort(403, 'Unauthorized.');
        }
    }

    private function extractApiKey(Request $request): ?string
    {
        $key = $request->header('X-Gemini-Api-Key')
            ?: $request->input('gemini_api_key')
            ?: $request->input('api_key');

        return ! empty($key) ? trim((string) $key) : null;
    }

    private function success(string $message = '', array $data = [], int $status = 200): JsonResponse
    {
        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $data), $status);
    }

    private function error(string $message = '', int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
