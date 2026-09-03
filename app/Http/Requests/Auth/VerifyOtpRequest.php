<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'otp' => 'required|string|digits:6',
        ];
    }

    public function messages(): array
    {
        return [
            'otp.required' => 'Please enter the 6-digit code.',
            'otp.digits'   => 'The verification code must be exactly 6 digits.',
        ];
    }
}
