<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:licenses,code',
            'min_age' => 'nullable|integer|min:18',
            'registration_fee' => 'required|integer|min:0',
            'required_documents' => 'required|array',
            'required_documents.*' => 'string',
            'requirements' => 'nullable|array',
        ];
    }

    public function messages()
{
    return [
        'code.required' => 'رمز الشهادة مطلوب.',
        'code.unique' => 'رمز الشهادة مستخدم بالفعل.',
        'min_age.min'=>'يجب ان يكون العمر 18 او اكبر',
        'registration_fee.required' => 'رسم التسجيل مطلوب.',
        'registration_fee.integer' => 'رسم التسجيل يجب أن يكون رقماً.',
        'required_documents.required' => 'يجب إدخال الأوراق المطلوبة.',
        'required_documents.array' => 'الأوراق المطلوبة يجب أن تكون في شكل قائمة.',
        'required_documents.*.string' => 'كل عنصر في الأوراق المطلوبة يجب أن يكون نصاً.',
        'requirements.array' => 'حقل الشروط يجب أن يكون في شكل قائمة أو كائن.',
    ];
}

}
