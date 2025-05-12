<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrainerRegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'license_number' => 'required|string|unique:trainers,license_number|min:6|max:20',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:30',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/'
            ],
        'phone_number' => 'required|string|digits:10|unique:trainers,phone_number',
            'license_expiry_date'=>'required|date|before_or_equal:today',
            'address' => 'required|string|max:255', 
            'experience'=>'required|string|max:255',
            'specialization'=>'required|in:regular,special_needs',
            'role' => 'required|in:trainer,student,employee',
            'gender' => 'required|in:female,male',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

        ];
    }

    public function messages()
    {
        return [

            'first_name.required' => 'الاسم الأول مطلوب.',
            'last_name.required' => 'اسم العائلة مطلوب.',
            'license_number.required' => 'رقم الاجازة مطلوب.',
            'license_number.unique' => 'رقم الاجازة مستخدم بالفعل.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.',
            'password.max' => 'يجب ألا تتجاوز كلمة المرور 30 حرفاً.',
            'password.regex' => 'يجب أن تحتوي كلمة المرور على حرف كبير وحرف صغير ورقم واحد على الأقل.',
            'experience.required' => 'الخبرات  مطلوب.',
            'experience.max' => 'يجب ألا يتجاوز العنوان 255 حرفاً.',
            'address.required' => 'العنوان مطلوب.',
            'address.max' => 'يجب ألا يتجاوز العنوان 255 حرفاً.',
          'phone_number.digits' => 'يجب أن يتكون رقم الهاتف من 10 أرقام فقط.',
           'phone_number.unique' => 'رقم الهاتف مسجل مسبقاً.',            'role.required' => 'الدور مطلوب.',
            'role.in' => 'يجب أن يكون الدور أحد: trainer أو student أو employee.',
            'gender.required' => 'الجنس مطلوب.',
            'gender.in' => 'يجب أن يكون الجنس أحد: female أو male ',
            'specialization.required' => 'التخصص مطلوب.',
            'specialization.in' => 'يجب أن يكون التخصص أحد: regular أو special_needs ',
            'image.image' => 'يجب رفع صورة سليمة.',
'image.mimes' => 'امتدادات الصور المسموحة: jpeg, png, jpg, gif.',
'license_expiry_date.required' => 'تاريخ انتهاء الصلاحية  مطلوب.',
'license_expiry_date.date' => 'يجب إدخال تاريخ صحيح.',
'license_expiry_date.before_or_equal' => 'يجب أن يكون تاريخ  في الماضي أو اليوم.',
        ];
    }
}