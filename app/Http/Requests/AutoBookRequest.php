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

        ];
    }
 public function messages(): array
    {
        return [
            'session_id.required' => 'يجب تحديد الجلسة.',
            'session_id.exists' => 'الجلسة غير موجودة.',
        ];
    }
   
}
