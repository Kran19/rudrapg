<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class CreateComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'in:PLUMBING,ELECTRICAL,WIFI,CLEANING,Plumbing,Electrical,Wi-Fi,Cleaning,plumbing,electrical,wifi,wi-fi'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', 'string', 'in:LOW,MEDIUM,HIGH,Low,Medium,High,low,medium,high'],
        ];
    }
}
