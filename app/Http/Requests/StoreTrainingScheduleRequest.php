<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrainingScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedules' => 'required|array|min:1',
            'schedules.*.trainer_id' => 'required|exists:trainers,id',
            'schedules.*.day_of_week' => [
                'required',
                Rule::in(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            ],
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.is_recurring' => 'boolean',
            'schedules.*.valid_from' => 'nullable|date',
            'schedules.*.valid_to' => 'nullable|date|after_or_equal:schedules.*.valid_from',
        ];
    }

    public function messages()
    {
        return [
            'schedules.required' => 'يجب تحديد جدول واحد على الأقل.',
            'schedules.*.trainer_id.required' => 'المدرب مطلوب.',
            'schedules.*.trainer_id.exists' => 'المدرب غير موجود.',
            'schedules.*.day_of_week.required' => 'اليوم مطلوب.',
            'schedules.*.day_of_week.in' => 'اليوم غير صالح.',
            'schedules.*.start_time.required' => 'وقت البداية مطلوب.',
            'schedules.*.end_time.required' => 'وقت النهاية مطلوب.',
            'schedules.*.end_time.after' => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
        ];
    }
}
