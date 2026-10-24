<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->has('MEMBER_LOGIN');
    }

    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'min:5', 'max:2000'],
            'aspect_ratio' => ['required', Rule::in(array_keys(config('image-generation.aspect_ratios')))],
            'quality' => ['required', Rule::in(array_keys(config('image-generation.qualities')))],
        ];
    }

    public function messages(): array
    {
        return [
            'prompt.required' => 'Describe the image you want to create.',
            'prompt.min' => 'Please add a little more detail to your prompt.',
        ];
    }
}
