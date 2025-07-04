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
            'address' => 'required|string|max:255', 
                       'date_of_Birth' => 'required|date|before_or_equal:today',

            'role' => 'required|in:trainer,student,employee',
            'gender' => 'required|in:female,male',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
'license_number' => [
    'required',
    'regex:/^\d{6}$/',
    'unique:trainers,license_number'
],
        'license_expiry_date' => 'required|date|after_or_equal:today',
        'experience' => 'required|string|max:255',
        'training_type' => 'required|in:normal,special_needs',
        ];
    }

    public function messages()
    {
        return [

            'first_name.required' => 'الاسم الأول مطلوب.',
            'last_name.required' => 'اسم العائلة مطلوب.',
           
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.',
            'password.max' => 'يجب ألا تتجاوز كلمة المرور 30 حرفاً.',
            'password.regex' => 'يجب أن تحتوي كلمة المرور على حرف كبير وحرف صغير ورقم واحد على الأقل.',
            'license_number.regex' => 'يجب أن يتكون رقم الرخصة من 6 أرقام.',

            'address.required' => 'العنوان مطلوب.',
            'address.max' => 'يجب ألا يتجاوز العنوان 255 حرفاً.',
          'phone_number.digits' => 'يجب أن يتكون رقم الهاتف من 10 أرقام فقط.',
           'phone_number.unique' => 'رقم الهاتف مسجل مسبقاً.',            'role.required' => 'الدور مطلوب.',
            'role.in' => 'يجب أن يكون الدور أحد: trainer أو student أو employee.',
            'gender.required' => 'الجنس مطلوب.',
            'gender.in' => 'يجب أن يكون الجنس أحد: female أو male ',
           'license_number.required' => 'رقم الرخصة مطلوب.',
        'license_number.unique' => 'رقم الرخصة مسجل مسبقاً.',
        'license_expiry_date.required' => 'تاريخ انتهاء الرخصة مطلوب.',
        'license_expiry_date.after_or_equal' => 'يجب أن يكون تاريخ انتهاء الرخصة اليوم أو بعده.',
            'image.image' => 'يجب رفع صورة سليمة.',
            'training_type.required' => 'نوع التدريب مطلوب.',
        'training_type.in' => 'يجب أن يكون نوع التدريب إما "normal" أو "special_needs".',
'image.mimes' => 'امتدادات الصور المسموحة: jpeg, png, jpg, gif.',
  'date_of_Birth.date' => 'يجب إدخال تاريخ صحيح.',
            'date_of_Birth.before_or_equal' => 'يجب أن يكون تاريخ الولادة في الماضي أو اليوم.',
        ];
    }
}