<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StudentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
{
    $studentId = $this->route('student');
    
    if (is_string($studentId)) {
        $student = \App\Models\Student::with('user')->find($studentId);
        
        if (!$student) {
            throw ValidationException::withMessages([
                'student' => 'الطالب المطلوب غير موجود.'
            ]);
        }
        
        $this->route()->setParameter('student', $student);
    }
}

public function rules(): array
{
    $student = $this->route('student');
    
    if (!$student || !$student->user) {
        throw ValidationException::withMessages([
            'student' => 'بيانات المستخدم للطالب غير موجودة.'
        ]);
    }

    $rules = [
        'first_name' => 'sometimes|string|max:255',
        'last_name' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:users,email,' . $student->user->id,
  'password' => [
                'sometimes',
                'confirmed',
                'string',
                'min:8',
                'max:30',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/'
            ],        'phone_number' => 'sometimes|string|digits:10|unique:students,phone_number',
        'date_of_Birth' =>'sometimes|date|before_or_equal:today',
        'gender' => 'sometimes|in:female,male',
        'address' => 'sometimes|string|max:100',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
    ];

    return $rules;
}
  public function messages(): array
    {
        return [
            'first_name.string' => 'يجب أن يكون الاسم الأول نصياً.',
            'first_name.max' => 'يجب ألا يتجاوز الاسم الأول 255 حرفاً.',
'password.min' => 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.',
            'password.max' => 'يجب ألا تتجاوز كلمة المرور 30 حرفاً.',
            'password.regex' => 'يجب أن تحتوي كلمة المرور على حرف كبير وحرف صغير ورقم واحد على الأقل.',
            'last_name.string' => 'يجب أن يكون اسم العائلة نصياً.',
            'last_name.max' => 'يجب ألا يتجاوز اسم العائلة 255 حرفاً.',

            'email.email' => 'يجب إدخال بريد إلكتروني صالح.',
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل مدرب آخر.',

            'password.min' => 'يجب أن تتكون كلمة المرور من 6 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',

            'phone_number.string' => 'يجب أن يكون رقم الهاتف نصياً.',
            'phone_number.digits' => 'يجب أن يتكون رقم الهاتف من 10 أرقام بالضبط.',
            'phone_number.unique' => 'رقم الهاتف مستخدم من قبل مدرب آخر.',

            'date_of_Birth.date' => 'يجب إدخال تاريخ صحيح.',
            'date_of_Birth.before_or_equal' => 'يجب أن يكون تاريخ الولادة في الماضي أو اليوم.',

           

            'gender.in' => 'يجب أن يكون الجنس إما "أنثى" أو "ذكر".',

            'address.string' => 'يجب أن يكون العنوان نصياً.',
            'address.max' => 'يجب ألا يتجاوز العنوان 100 حرفاً.',

           

            'image.image' => 'يجب أن يكون الملف المرفوع صورة.',
            'image.mimes' => 'يجب أن تكون الصورة من نوع: jpeg, png, jpg, gif, svg.',
            'image.max' => 'يجب ألا تتجاوز حجم الصورة 2 ميجابايت.',
        ];
    }
}
