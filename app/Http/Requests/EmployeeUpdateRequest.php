<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class EmployeeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    
    protected function prepareForValidation(): void
    {
        if (is_string($this->route('employee'))) {
            $this->route()->setParameter(
                'employee',
                \App\Models\Employee::findOrFail($this->route('employee'))
            );
        }

        
       
    }

    
    public function rules(): array
    {
        $employee = $this->route('employee');

        $rules = [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $employee->user_id,
            'password' => 'sometimes|min:6|confirmed',
        'phone_number' => 'sometimes|string|digits:10|unique:employees,phone_number',
            'address' => 'sometimes|string|max:100',
            'hire_date'=>'sometimes|date|before_or_equal:today',
            'gender'=>'sometimes|in:female,male'
        ];

        

        return $rules;
    }public function messages(): array
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

           'hire_date.date' => 'يجب إدخال تاريخ صحيح.',
            'hire_date.before_or_equal' => 'يجب أن يكون تاريخ التعيين في الماضي أو اليوم.',

           

            'gender.in' => 'يجب أن يكون الجنس إما "أنثى" أو "ذكر".',

            'address.string' => 'يجب أن يكون العنوان نصياً.',
            'address.max' => 'يجب ألا يتجاوز العنوان 100 حرفاً.',

           

            'image.image' => 'يجب أن يكون الملف المرفوع صورة.',
            'image.mimes' => 'يجب أن تكون الصورة من نوع: jpeg, png, jpg, gif, svg.',
            'image.max' => 'يجب ألا تتجاوز حجم الصورة 2 ميجابايت.',
        ];
    }
}
