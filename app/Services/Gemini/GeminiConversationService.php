<?php

namespace App\Services\Gemini;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiUsageLog;
use Illuminate\Support\Str;

class GeminiConversationService
{
    public function create(?int $memberId, string $mode = 'voice'): AiConversation
    {
        return AiConversation::create([
            'member_id' => $memberId,  // null = guest / unauthenticated
            'title'     => 'JARVIS Conversation',
            'model'     => $mode === 'voice' ? config('gemini.live_model') : config('gemini.default_model'),
            'mode'      => $mode,
            'started_at'      => now(),
            'last_message_at' => now(),
        ]);
    }

    public function latestOrCreate(?int $memberId, string $mode = 'voice'): AiConversation
    {
        if ($memberId !== null) {
            $existing = AiConversation::where('member_id', $memberId)
                ->orderBy('last_message_at', 'desc')
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return $this->create($memberId, $mode);
    }

    public function owned(string $id, ?int $memberId): AiConversation
    {
        $query = AiConversation::whereKey($id);
        if ($memberId !== null) {
            $query->where('member_id', $memberId);
        }

        return $query->firstOrFail();
    }

    public function addMessage(AiConversation $conversation, string $role, ?string $content, string $inputType, string $outputType, array $metadata = []): AiMessage
    {
        $message = $conversation->messages()->create([
            'member_id' => $conversation->member_id,
            'role' => $role,
            'content' => $content,
            'input_type' => $inputType,
            'output_type' => $outputType,
            'metadata' => $metadata ?: null,
        ]);

        $changes = ['last_message_at' => now()];
        if ($role === 'user' && in_array($conversation->title, ['New conversation', 'JARVIS Conversation']) && filled($content)) {
            $changes['title'] = Str::limit(preg_replace('/\s+/', ' ', $content), 80, '');
        }
        $conversation->update($changes);

        return $message;
    }

    public function context(AiConversation $conversation, int $limit = 25): array
    {
        return $conversation->messages()->whereIn('role', ['user', 'assistant'])
            ->whereNotNull('content')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (AiMessage $message) => [
                'role' => $message->role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => Str::limit($message->content, 8000)]],
            ])
            ->all();
    }

    public function rawMessages(AiConversation $conversation, int $limit = 25): array
    {
        return $conversation->messages()->whereIn('role', ['user', 'assistant'])
            ->whereNotNull('content')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (AiMessage $message) => [
                'role' => $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at?->toIso8601String(),
            ])
            ->all();
    }

    public function logUsage(AiConversation $conversation, ?AiMessage $message, string $requestType, string $inputType, string $outputType, array $usage, string $status = 'success', ?string $errorType = null): void
    {
        AiUsageLog::create([
            'member_id' => $conversation->member_id,
            'conversation_id' => $conversation->id,
            'message_id' => $message?->id,
            'model' => $conversation->model,
            'request_type' => $requestType,
            'input_type' => $inputType,
            'output_type' => $outputType,
            'input_tokens' => $usage['input_tokens'] ?? null,
            'output_tokens' => $usage['output_tokens'] ?? null,
            'total_tokens' => $usage['total_tokens'] ?? null,
            'duration_ms' => $usage['duration_ms'] ?? null,
            'status' => $status,
            'error_type' => $errorType,
        ]);
    }
}
