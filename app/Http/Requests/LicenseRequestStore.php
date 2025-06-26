<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseRequestStore extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'license_code' => 'required|exists:licenses,code',
            'type' => 'required|in:new,renewal,replacement',
            'notes' => 'nullable|string',
            'required_documents' => 'required|array',
            'required_documents.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'license_code.required' => 'يجب إدخال كود الرخصة.',
            'license_code.exists' => 'كود الرخصة غير صحيح أو غير موجود في النظام.',

            'type.required' => 'يجب تحديد نوع الطلب (جديد / تجديد / بدل ضائع).',
            'type.in' => 'نوع الطلب غير صحيح. يجب أن يكون: جديد، تجديد، أو استبدال.',

            'notes.string' => 'يجب أن تكون الملاحظات نصًا.',

            'required_documents.required' => 'يجب رفع المستندات المطلوبة.',
            'required_documents.array' => 'يجب أن تكون المستندات المطلوبة على شكل قائمة (مصفوفة).',

            'required_documents.*.file' => 'يجب أن يكون كل عنصر ملفًا صالحًا.',
            'required_documents.*.mimes' => 'يجب أن يكون الملف من نوع: jpg, jpeg, png, أو pdf.',
            'required_documents.*.max' => 'يجب ألا يتجاوز حجم الملف 2 ميجابايت.',
        ];
    }
}