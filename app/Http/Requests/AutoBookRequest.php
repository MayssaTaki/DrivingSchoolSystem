<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutoBookRequest extends FormRequest
{
    public function authorize(): bool
    {
         return true;
    }

    public function rules(): array
    {
        return [
        'session_id' => ['required', 'exists:training_sessions,id'],
        'transmission' => ['required', 'in:automatic,manual'],
        'is_for_special_needs' => ['required', 'boolean'],

        ];
    }
 public function messages(): array
    {
        return [
            'session_id.required' => 'يجب تحديد الجلسة.',
            'session_id.exists' => 'الجلسة غير موجودة.',
            'transmission.required' => 'نوع ناقل الحركة مطلوب.',
            'transmission.in' => 'نوع ناقل الحركة يجب أن يكون أوتوماتيك أو عادي.',
            'is_for_special_needs.required' => 'حقل الاحتياجات الخاصة مطلوب.',
            'is_for_special_needs.boolean' => 'حقل الاحتياجات الخاصة يجب أن يكون نعم أو لا.',
        ];
    }
   
}
