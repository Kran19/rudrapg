<?php

namespace App\Http\Requests\SubAdmin;

use Illuminate\Foundation\Http\FormRequest;

class ApproveRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'bed_id' => ['required', 'exists:beds,id'],
            'joining_date' => ['nullable', 'date'],
        ];
    }
}
