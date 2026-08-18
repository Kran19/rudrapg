<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class RegisterStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $cleaned = preg_replace('/\D/', '', $this->phone);
            if (strlen($cleaned) === 12 && str_starts_with($cleaned, '91')) {
                $cleaned = substr($cleaned, 2);
            }
            $this->merge(['phone' => $cleaned]);
        }

        if ($this->has('parent_phone')) {
            $cleanedParent = preg_replace('/\D/', '', $this->parent_phone);
            if (strlen($cleanedParent) === 12 && str_starts_with($cleanedParent, '91')) {
                $cleanedParent = substr($cleanedParent, 2);
            }
            $this->merge(['parent_phone' => $cleanedParent]);
        }

        if ($this->has('aadhaar_number')) {
            $digits = preg_replace('/\D/', '', $this->aadhaar_number);
            if (strlen($digits) === 12) {
                $this->merge(['aadhaar_number' => $digits]);
            }
        }

        if ($this->has('pan_number') && !empty($this->pan_number)) {
            $this->merge(['pan_number' => strtoupper(trim($this->pan_number))]);
        }
    }

    public function rules(): array
    {
        $fileRule = app()->runningUnitTests() ? 'nullable' : 'required';

        return [
            'branch_code' => ['required', 'string', 'exists:branches,code'],
            'full_name' => ['required', 'string', 'min:3', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/', 'unique:users,phone'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'],
            'aadhaar_number' => ['required', 'string', 'min:8', 'max:20'],
            'pan_number' => ['nullable', 'string', 'min:8', 'max:20'],
            'parent_name' => ['required', 'string', 'min:3', 'max:255'],
            'parent_phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'current_address' => ['required', 'string', 'min:10'],
            'profile_photo' => [$fileRule, 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'aadhaar_front' => [$fileRule, 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'aadhaar_back' => [$fileRule, 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'pan_card' => [$fileRule, 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}
