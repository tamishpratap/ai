<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssistantLiveTurnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conversation_id' => ['required', 'uuid'],
            'turn_id' => ['required', 'uuid'],
            'user_transcript' => ['nullable', 'string', 'max:16000', 'required_without:assistant_transcript'],
            'assistant_transcript' => ['nullable', 'string', 'max:30000', 'required_without:user_transcript'],
            'duration_ms' => ['nullable', 'integer', 'min:0', 'max:3600000'],
            'usage' => ['nullable', 'array'],
            'usage.promptTokenCount' => ['nullable', 'integer', 'min:0'],
            'usage.responseTokenCount' => ['nullable', 'integer', 'min:0'],
            'usage.totalTokenCount' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

