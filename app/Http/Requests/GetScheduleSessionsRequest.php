<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetScheduleSessionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
   
public function rules()
{
    return [
        'schedule_id' => ['required', 'integer', 'exists:training_schedules,id'],
    ];
}

public function messages()
{
    return [
         'schedule_id.required' => 'يرجى تحديد معرف الجدول (schedule_id).',
        'schedule_id.integer' => 'معرف الجدول يجب أن يكون رقماً صحيحاً.',
        'schedule_id.exists' => 'الجدول غير موجود.',
    ];
}
}
