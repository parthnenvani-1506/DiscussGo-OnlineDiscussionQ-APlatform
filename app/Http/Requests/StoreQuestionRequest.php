<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'             => 'required|string|min:15|max:300',
            // Either select existing category OR type a new one
            'category_id'       => 'nullable|exists:categories,id',
            'new_category_name' => 'nullable|string|max:100',
            // Description is now optional
            'description'       => 'nullable|string|max:50000',
            // Tags: mix of existing IDs and new tag names
            'tag_ids'           => 'nullable|array|max:5',
            'tag_ids.*'         => 'integer|exists:tags,id',
            'new_tags'          => 'nullable|array|max:5',
            'new_tags.*'        => 'string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'title.min'               => 'The title should be at least 15 characters.',
            'title.required'          => 'A title is required.',
            'category_id.exists'      => 'Please select a valid category.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Must have either a category_id OR a new_category_name
            if (empty($this->category_id) && empty($this->new_category_name)) {
                $validator->errors()->add('category_id', 'Please select or create a category.');
            }
        });
    }
}
