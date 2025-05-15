<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleExceptionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'exception_date' => 'required|date',
            'is_available' => 'required|boolean',
            'available_start_time' => 'required_if:is_available,true|nullable|date_format:H:i',
            'available_end_time' => 'required_if:is_available,true|nullable|date_format:H:i|after:available_start_time',
            'reason' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'exception_date.required' => 'حقل تاريخ الاستثناء مطلوب.',
            'exception_date.date' => 'يجب أن يكون تاريخ الاستثناء تاريخًا صالحًا.',
            'is_available.required' => 'حقل حالة التوفر مطلوب.',
            'is_available.boolean' => 'يجب أن تكون حالة التوفر صحيحة أو خاطئة.',
            'available_start_time.required_if' => 'حقل وقت البدء مطلوب عند التوفر.',
            'available_start_time.date_format' => 'صيغة وقت البدء غير صالحة.',
            'available_end_time.required_if' => 'حقل وقت الانتهاء مطلوب عند التوفر.',
            'available_end_time.date_format' => 'صيغة وقت الانتهاء غير صالحة.',
            'available_end_time.after' => 'يجب أن يكون وقت الانتهاء بعد وقت البدء.',
            'reason.max' => 'يجب ألا يتجاوز السبب 500 حرف.',
        ];
    }
}