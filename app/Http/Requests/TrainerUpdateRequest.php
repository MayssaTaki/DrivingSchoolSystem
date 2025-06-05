<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TrainerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->route('trainer'))) {
            $this->route()->setParameter(
                'trainer',
                \App\Models\Trainer::findOrFail($this->route('trainer'))
            );
        }
    }

    public function rules(): array
    {
        $trainer = $this->route('trainer');

        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'password' => 'sometimes|min:6|confirmed',
'password' => [
                'sometimes',
                
                'string',
                'min:8',
                'max:30',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/'
            ],              'gender' => 'sometimes|in:female,male',
            'address' => 'sometimes|string|max:100',
                   'license_number' => [
    'sometimes',
    'regex:/^\d{7}$/',
    'unique:trainers,license_number'
],
            'date_of_Birth' =>'sometimes|date|before_or_equal:today',
'license_expiry_date' => 'sometimes|date|after_or_equal:today',
        'experience' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.string' => 'يجب أن يكون الاسم الأول نصياً.',
            'first_name.max' => 'يجب ألا يتجاوز الاسم الأول 255 حرفاً.',

            'last_name.string' => 'يجب أن يكون اسم العائلة نصياً.',
            'last_name.max' => 'يجب ألا يتجاوز اسم العائلة 255 حرفاً.',


'password.min' => 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.',
            'password.max' => 'يجب ألا تتجاوز كلمة المرور 30 حرفاً.',
            'password.regex' => 'يجب أن تحتوي كلمة المرور على حرف كبير وحرف صغير ورقم واحد على الأقل.',            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',

            'phone_number.string' => 'يجب أن يكون رقم الهاتف نصياً.',
            'phone_number.digits' => 'يجب أن يتكون رقم الهاتف من 10 أرقام بالضبط.',
            'phone_number.unique' => 'رقم الهاتف مستخدم من قبل مدرب آخر.',

          

            'gender.in' => 'يجب أن يكون الجنس إما "أنثى" أو "ذكر".',

            'address.string' => 'يجب أن يكون العنوان نصياً.',
            'address.max' => 'يجب ألا يتجاوز العنوان 100 حرفاً.',
 'date_of_Birth.date' => 'يجب إدخال تاريخ صحيح.',
            'date_of_Birth.before_or_equal' => 'يجب أن يكون تاريخ الولادة في الماضي أو اليوم.',
            'image.image' => 'يجب أن يكون الملف المرفوع صورة.',
            'image.mimes' => 'يجب أن تكون الصورة من نوع: jpeg, png, jpg, gif, svg.',
            'license_number.regex' => 'يجب أن يتكون رقم الرخصة من 7 أرقام.',

            'image.max' => 'يجب ألا تتجاوز حجم الصورة 2 ميجابايت.',
        ];
    }
}