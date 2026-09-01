<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'question_id' => 'required|exists:questions,id',
            'answer' => 'required|string|min:10',
        ];
    }

    public function messages(): array
    {
        return [
            'answer.min' => 'Please provide a helpful answer with at least 10 characters.',
        ];
    }
}
