<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrainingScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedules' => 'required|array|min:1',
            'schedules.*.id' => 'required|exists:training_schedules,id',
            'schedules.*.day_of_week' => [
                'sometimes',
                Rule::in(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            ],
            'schedules.*.start_time' => [
                'sometimes',
                'date_format:H:i',
                'after_or_equal:09:00',
                'before_or_equal:20:00',
            ],
            'schedules.*.end_time' => [
                'sometimes',
                'date_format:H:i',
                'after:schedules.*.start_time',
                'after_or_equal:09:00',
                'before_or_equal:20:00',
            ],
            'schedules.*.is_recurring' => 'boolean',
            'schedules.*.valid_from' => 'nullable|date',
            'schedules.*.valid_to' => 'nullable|date|after_or_equal:schedules.*.valid_from',
            'schedules.*.status' => 'in:active,inactive',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $schedules = $this->input('schedules', []);

            foreach ($schedules as $index => $schedule) {
                $start = $schedule['start_time'] ?? null;
                $end = $schedule['end_time'] ?? null;

                if ($start && $end) {
                    $startMinutes = $this->convertToMinutes($start);
                    $endMinutes = $this->convertToMinutes($end);

                    if (($endMinutes - $startMinutes) < 60) {
                        $validator->errors()->add("schedules.$index.end_time", 'مدة الحصة يجب أن تكون ساعة واحدة على الأقل.');
                    }
                }
            }
        });
    }

    private function convertToMinutes($time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return ((int)$hours * 60) + (int)$minutes;
    }

    public function messages()
    {
        return [
            'schedules.required' => 'يجب تقديم جداول للتعديل.',
            'schedules.*.id.required' => 'معرف الجدول مطلوب.',
            'schedules.*.id.exists' => 'الجدول غير موجود.',
            'schedules.*.day_of_week.in' => 'اليوم غير صالح.',
            'schedules.*.start_time.date_format' => 'صيغة وقت البداية غير صحيحة (مثال: 09:00).',
            'schedules.*.start_time.after_or_equal' => 'وقت البداية يجب ألا يقل عن 09:00 صباحاً.',
            'schedules.*.start_time.before_or_equal' => 'وقت البداية يجب ألا يتجاوز 20:00 مساءً.',
            'schedules.*.end_time.date_format' => 'صيغة وقت النهاية غير صحيحة (مثال: 10:00).',
            'schedules.*.end_time.after' => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
            'schedules.*.end_time.after_or_equal' => 'وقت النهاية يجب ألا يقل عن 09:00 صباحاً.',
            'schedules.*.end_time.before_or_equal' => 'وقت النهاية يجب ألا يتجاوز 20:00 مساءً.',
            'schedules.*.valid_to.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البداية.',
            'schedules.*.status.in' => 'الحالة يجب أن تكون إما "active" أو "inactive".',
        ];
    }
}
