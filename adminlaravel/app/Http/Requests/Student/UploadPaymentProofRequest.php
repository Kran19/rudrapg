<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'utr_number' => ['required', 'string', 'max:50'],
            'amount' => ['nullable', 'numeric'],
            'payment_type' => ['nullable', 'string', 'in:RENT,DEPOSIT,ELECTRICITY'],
        ];
    }
}
