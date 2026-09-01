<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'             => 'required|string|min:15|max:300',
            'category_id'       => 'nullable|exists:categories,id',
            'new_category_name' => 'nullable|string|max:100',
            'description'       => 'nullable|string|max:50000',
            'tag_ids'           => 'nullable|array|max:5',
            'tag_ids.*'         => 'integer|exists:tags,id',
            'new_tags'          => 'nullable|array|max:5',
            'new_tags.*'        => 'string|max:50',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (empty($this->category_id) && empty($this->new_category_name)) {
                $validator->errors()->add('category_id', 'Please select or create a category.');
            }
        });
    }
}
