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
            'schedules.*.trainer_id' => 'required|exists:trainers,id',
            'schedules.*.day_of_week' => [
                'sometimes',
                Rule::in(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday']),
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
            'schedules.*.is_recurring' => 'sometimes|boolean',
            'schedules.*.valid_from' => [
                'sometimes',
                'date',
                'after_or_equal:' . now()->startOfYear()->toDateString(),
                'after_or_equal:' . now()->toDateString(),
                Rule::requiredIf(function () {
                    $schedules = $this->input('schedules', []);
                    foreach ($schedules as $schedule) {
                        if (($schedule['is_recurring'] ?? false) === true) {
                            return true;
                        }
                    }
                    return false;
                }),
            ],
            'schedules.*.valid_to' => [
                'sometimes',
                'date',
                'after_or_equal:schedules.*.valid_from',
                'before_or_equal:' . now()->endOfYear()->toDateString(),
                Rule::requiredIf(function () {
                    $schedules = $this->input('schedules', []);
                    foreach ($schedules as $schedule) {
                        if (($schedule['is_recurring'] ?? false) === true) {
                            return true;
                        }
                    }
                    return false;
                }),
            ],
            'schedules.*.status' => 'sometimes|in:active,inactive',
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

                if (($schedule['is_recurring'] ?? false) === true) {
                    $validFrom = $schedule['valid_from'] ?? null;
                    $validTo = $schedule['valid_to'] ?? null;

                    if (!$validFrom || !$validTo) {
                        $validator->errors()->add("schedules.$index.valid_from", 'يجب تحديد تاريخي البداية والنهاية للجلسات المتكررة.');
                        continue;
                    }

                    if (strtotime($validTo) <= strtotime($validFrom)) {
                        $validator->errors()->add("schedules.$index.valid_to", 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البداية.');
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
            'schedules.*.trainer_id.exists' => 'المدرب غير موجود.',
            'schedules.*.day_of_week.in' => 'اليوم غير صالح.',
            'schedules.*.start_time.date_format' => 'صيغة وقت البداية غير صحيحة (مثال: 09:00).',
            'schedules.*.start_time.after_or_equal' => 'وقت البداية يجب ألا يقل عن 09:00 صباحاً.',
            'schedules.*.start_time.before_or_equal' => 'وقت البداية يجب ألا يتجاوز 20:00 مساءً.',
            'schedules.*.end_time.date_format' => 'صيغة وقت النهاية غير صحيحة (مثال: 10:00).',
            'schedules.*.end_time.after' => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
            'schedules.*.end_time.after_or_equal' => 'وقت النهاية يجب ألا يقل عن 09:00 صباحاً.',
            'schedules.*.end_time.before_or_equal' => 'وقت النهاية يجب ألا يتجاوز 20:00 مساءً.',
            'schedules.*.valid_from.required' => 'تاريخ البداية مطلوب للجلسات المتكررة.',
            'schedules.*.valid_from.date' => 'صيغة تاريخ البداية غير صحيحة.',
            'schedules.*.valid_from.after_or_equal' => 'تاريخ البداية يجب أن يكون ضمن السنة الحالية وبعد تاريخ إنشاء الجدول.',
            'schedules.*.valid_to.required' => 'تاريخ الانتهاء مطلوب للجلسات المتكررة.',
            'schedules.*.valid_to.date' => 'صيغة تاريخ الانتهاء غير صحيحة.',
            'schedules.*.valid_to.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البداية.',
            'schedules.*.valid_to.before_or_equal' => 'تاريخ الانتهاء يجب أن يكون ضمن السنة الحالية.',
            'schedules.*.status.in' => 'الحالة يجب أن تكون إما "active" أو "inactive".',
        ];
    }
}