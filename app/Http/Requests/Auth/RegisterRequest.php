<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_name' => 'required|string|min:3|max:50|unique:users,user_name',
            'email' => 'required|string|email|max:191|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'city' => 'nullable|string|max:100',
        ];
    }
}
