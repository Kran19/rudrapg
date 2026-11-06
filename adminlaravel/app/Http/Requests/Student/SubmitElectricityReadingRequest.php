<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitElectricityReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_reading' => ['required', 'numeric', 'min:0'],
            'meter_photo_path' => ['nullable'],
        ];
    }
}
