<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required_without:phone', 'string'],
            'phone' => ['required_without:email', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
