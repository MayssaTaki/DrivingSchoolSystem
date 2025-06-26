<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRegisterRequest extends FormRequest
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
            'date_of_Birth' => 'required|date|before_or_equal:today',
        'phone_number' => 'required|string|digits:10|unique:students,phone_number',
            'address' => 'required|string|max:255', 
'nationality' => 'nullable|string|max:255',
'is_military' => 'required|boolean',

            'role' => 'required|in:trainer,student,employee',
            'gender' => 'required|in:female,male',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
'left_hand_disabled'=>'required|boolean', 

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
            'address.required' => 'العنوان مطلوب.',
            'address.max' => 'يجب ألا يتجاوز العنوان 255 حرفاً.',
            'phone_number.digits' => 'يجب أن يتكون رقم الهاتف من 10 أرقام فقط.',
            'phone_number.unique' => 'رقم الهاتف مسجل مسبقاً.',            'date_of_Birth.required' => 'تاريخ الولادة مطلوب.',
            'date_of_Birth.date' => 'يجب إدخال تاريخ صحيح.',
            'date_of_Birth.before_or_equal' => 'يجب أن يكون تاريخ الولادة في الماضي أو اليوم.',
            'role.required' => 'الدور مطلوب.',
            'role.in' => 'يجب أن يكون الدور أحد: trainer أو student أو employee.',
            'gender.required' => 'الجنس مطلوب.',
            'nationality.string' => 'يجب أن تكون الجنسية نصاً.',
'nationality.max' => 'يجب ألا تتجاوز الجنسية 255 حرفاً.',
'is_military.required' => 'مطلوب تحديد هل هو مطلوب للخدمة العسكرية.',
'is_military.boolean' => 'حقل الخدمة العسكرية يجب أن يكون نعم أو لا.',

             'left_hand_disabled.boolean' => 'حقل ازا تملك اعاقة باليد اليسرى يجب أن يكون نعم أو لا.',
            'left_hand_disabled.required'=>'حقل ازا تملك اعاقة باليد اليسرى مطلوب.',
            'gender.in' => 'يجب أن يكون الجنس أحد: female أو male ',
        ];
    }
}