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
            'email' => 'sometimes|email|unique:users,email,' . $trainer->user_id,
            'password' => 'sometimes|min:6|confirmed',
            'phone_number' => 'sometimes|string|digits:10|unique:trainers,phone_number,' . $trainer->id,
            'license_number' => 'sometimes|string|max:50',
            'specialization' => 'sometimes|in:regular,special_needs',
            'experience' => 'sometimes|string|max:255',
            'gender' => 'sometimes|in:female,male',
            'address' => 'sometimes|string|max:100',
            'license_expiry_date' => 'sometimes|date|before_or_equal:today',
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

            'email.email' => 'يجب إدخال بريد إلكتروني صالح.',
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل مدرب آخر.',

            'password.min' => 'يجب أن تتكون كلمة المرور من 6 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',

            'phone_number.string' => 'يجب أن يكون رقم الهاتف نصياً.',
            'phone_number.digits' => 'يجب أن يتكون رقم الهاتف من 10 أرقام بالضبط.',
            'phone_number.unique' => 'رقم الهاتف مستخدم من قبل مدرب آخر.',

            'license_number.string' => 'يجب أن يكون رقم الرخصة نصياً.',
            'license_number.max' => 'يجب ألا يتجاوز رقم الرخصة 50 حرفاً.',

            'specialization.in' => 'يجب أن يكون التخصص إما "عادي" أو "احتياجات خاصة".',

            'experience.string' => 'يجب أن تكون الخبرة نصية.',
            'experience.max' => 'يجب ألا تتجاوز الخبرة 255 حرفاً.',

            'gender.in' => 'يجب أن يكون الجنس إما "أنثى" أو "ذكر".',

            'address.string' => 'يجب أن يكون العنوان نصياً.',
            'address.max' => 'يجب ألا يتجاوز العنوان 100 حرفاً.',

            'license_expiry_date.date' => 'يجب إدخال تاريخ صالح.',
            'license_expiry_date.before_or_equal' => 'يجب أن يكون تاريخ انتهاء الرخصة تاريخاً سابقاً أو يوماً الحالي.',

            'image.image' => 'يجب أن يكون الملف المرفوع صورة.',
            'image.mimes' => 'يجب أن تكون الصورة من نوع: jpeg, png, jpg, gif, svg.',
            'image.max' => 'يجب ألا تتجاوز حجم الصورة 2 ميجابايت.',
        ];
    }
}