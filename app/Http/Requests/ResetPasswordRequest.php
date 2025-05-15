<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
               'password' => [
                'required',
                'string',
                'min:8',
                'max:30',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/',
                'confirmed'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'البريد الإلكتروني غير مسجل.',
             'password.min' => 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.',
            'password.max' => 'يجب ألا تتجاوز كلمة المرور 30 حرفاً.',
            'password.regex' => 'يجب أن تحتوي كلمة المرور على حرف كبير وحرف صغير ورقم واحد على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ];
    }
}
