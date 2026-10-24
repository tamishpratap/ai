<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssistantChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conversation_id' => ['nullable', 'uuid'],
            'message' => ['nullable', 'string', 'max:8000', 'required_without:quick_action'],
            'quick_action' => ['nullable', Rule::in(array_keys(config('gemini.quick_actions'))), 'required_without:message'],
            'speak' => ['nullable', 'boolean'],
        ];
    }
}

