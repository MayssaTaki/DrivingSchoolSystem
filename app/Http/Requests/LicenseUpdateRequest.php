<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|string|unique:licenses,code,' . $this->route('license'),
            'min_age' => 'nullable|integer|min:18',
            'registration_fee' => 'sometimes|integer|min:0',
            'required_documents' => 'sometimes|array',
            'required_documents.*' => 'string',
            'requirements' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'min_age.min'=>'يجب ان يكون العمر 18 او اكبر',
            'code.unique' => 'رمز الشهادة مستخدم بالفعل.',
            'registration_fee.integer' => 'رسم التسجيل يجب أن يكون رقماً.',
            'required_documents.array' => 'الأوراق المطلوبة يجب أن تكون في شكل قائمة.',
            'required_documents.*.string' => 'كل عنصر في الأوراق المطلوبة يجب أن يكون نصاً.',
            'requirements.array' => 'حقل الشروط يجب أن يكون في شكل قائمة أو كائن.',
        ];
    }
}
