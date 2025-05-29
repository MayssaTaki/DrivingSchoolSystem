<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RecommendedSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preferred_date' => ['required', 'date'],
            'preferred_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if ($value < '09:00' || $value > '20:00') {
                        $fail('يجب أن يكون الوقت بين الساعة 09:00 صباحاً و 08:00 مساءً.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'preferred_date.required' => 'يرجى إدخال التاريخ المفضل.',
            'preferred_date.date' => 'صيغة التاريخ غير صحيحة.',

            'preferred_time.required' => 'يرجى إدخال الوقت المفضل.',
            'preferred_time.date_format' => 'صيغة الوقت يجب أن تكون على الشكل HH:MM مثل 14:00.',
        ];
    }
}
