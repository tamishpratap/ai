<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssistantPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conversation_id' => ['nullable', 'uuid'],
            'document' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.config('gemini.pdf_max_kb')],
            'prompt' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

