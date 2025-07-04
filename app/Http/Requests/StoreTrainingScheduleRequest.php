<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingScheduleRequest extends FormRequest
{
   public function authorize(): bool
{
    $trainer = auth()->user()->trainer;

    if (!$trainer) {
        return false;
    }

    $schedules = $this->input('schedules', []);

    foreach ($schedules as $schedule) {
        if (($schedule['trainer_id'] ?? null) != $trainer->id) {
            return false;
        }
    }

    return true;
}


    public function rules(): array
    {
        return [
            'schedules' => 'required|array|min:1',

            'schedules.*.trainer_id' => 'required|exists:trainers,id',

            'schedules.*.day_of_week' => [
                'required',
                'in:saturday,sunday,monday,tuesday,wednesday,thursday',
            ],

            'schedules.*.start_time' => [
                'required',
                'date_format:H:i',
                'after_or_equal:09:00',
                'before_or_equal:20:00',
            ],

            'schedules.*.end_time' => [
                'required',
                'date_format:H:i',
                'after:schedules.*.start_time',
                'after_or_equal:09:00',
                'before_or_equal:20:00',
            ],

            'schedules.*.is_recurring' => 'boolean',

            'schedules.*.valid_from' => [
                'required',
                'date',
                'after_or_equal:' . now()->toDateString(),
            ],

            'schedules.*.valid_to' => [
                'required',
                'date',
                'after_or_equal:schedules.*.valid_from',
                'before_or_equal:' . now()->endOfYear()->toDateString(),
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $schedules = $this->input('schedules', []);

            foreach ($schedules as $index => $schedule) {
                $start = $schedule['start_time'] ?? null;
                $end = $schedule['end_time'] ?? null;
                $validFrom = $schedule['valid_from'] ?? null;
                $validTo = $schedule['valid_to'] ?? null;

                if ($start && $end) {
                    $startMinutes = $this->convertToMinutes($start);
                    $endMinutes = $this->convertToMinutes($end);
                    $duration = $endMinutes - $startMinutes;

                    if ($duration < 60) {
                        $validator->errors()->add("schedules.$index.end_time", 'مدة الحصة يجب أن تكون ساعة واحدة على الأقل.');
                    }

                    if ($duration % 60 !== 0) {
                        $validator->errors()->add("schedules.$index.end_time", 'مدة الحصة يجب أن تكون من مضاعفات الساعة (60 دقيقة، 120، 180...).');
                    }
                }

                if ($validFrom && $validTo) {
                    if (strtotime($validTo) <= strtotime($validFrom)) {
                        $validator->errors()->add("schedules.$index.valid_to", 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البداية.');
                    }
                } else {
                    $validator->errors()->add("schedules.$index.valid_from", 'يجب تحديد تاريخي البداية والانتهاء.');
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
            'schedules.required' => 'يجب تحديد جدول واحد على الأقل.',
            'schedules.*.trainer_id.required' => 'المدرب مطلوب.',
            'schedules.*.trainer_id.exists' => 'المدرب غير موجود.',
            'schedules.*.day_of_week.required' => 'اليوم مطلوب.',
            'schedules.*.day_of_week.in' => 'اليوم غير صالح.',
            'schedules.*.start_time.required' => 'وقت البداية مطلوب.',
            'schedules.*.start_time.date_format' => 'صيغة وقت البداية غير صحيحة (مثال: 09:00).',
            'schedules.*.start_time.after_or_equal' => 'وقت البداية يجب ألا يقل عن 09:00 صباحاً.',
            'schedules.*.start_time.before_or_equal' => 'وقت البداية يجب ألا يتجاوز 20:00 مساءً.',
            'schedules.*.end_time.required' => 'وقت النهاية مطلوب.',
            'schedules.*.end_time.date_format' => 'صيغة وقت النهاية غير صحيحة (مثال: 10:00).',
            'schedules.*.end_time.after' => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
            'schedules.*.end_time.after_or_equal' => 'وقت النهاية يجب ألا يقل عن 09:00 صباحاً.',
            'schedules.*.end_time.before_or_equal' => 'وقت النهاية يجب ألا يتجاوز 20:00 مساءً.',
            'schedules.*.valid_from.required' => 'تاريخ البداية مطلوب.',
            'schedules.*.valid_from.date' => 'صيغة تاريخ البداية غير صحيحة.',
            'schedules.*.valid_from.after_or_equal' => 'تاريخ البداية يجب أن يكون اليوم أو بعده.',
            'schedules.*.valid_to.required' => 'تاريخ الانتهاء مطلوب.',
            'schedules.*.valid_to.date' => 'صيغة تاريخ الانتهاء غير صحيحة.',
            'schedules.*.valid_to.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البداية.',
            'schedules.*.valid_to.before_or_equal' => 'تاريخ الانتهاء يجب أن يكون ضمن هذه السنة.',
        ];
    }
}
