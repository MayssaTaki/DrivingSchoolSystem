<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\ScheduleException;

class ScheduleExceptionRequest extends FormRequest
{
    public function authorize(): bool
{
    $trainer = auth()->user()->trainer;

    if (!$trainer) {
        return false;
    }

    return $this->input('trainer_id') == $trainer->id;
}


    public function rules(): array
    {
        return [
            'trainer_id' => 'required|exists:trainers,id',
            'exception_dates' => 'required|array|min:1',
            'exception_dates.*' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:255',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator) {
            $trainerId = $this->input('trainer_id');
            $dates = $this->input('exception_dates', []);

            foreach ($dates as $index => $date) {
                $exists = ScheduleException::where('trainer_id', $trainerId)
                    ->whereDate('exception_date', $date)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add("exception_dates.$index", "يوجد بالفعل إجازة لهذا المدرب في {$date}.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'trainer_id.required' => 'يجب تحديد المدرب.',
            'trainer_id.exists' => 'المدرب غير موجود.',
            'exception_dates.required' => 'يجب تحديد تاريخ واحد على الأقل.',
            'exception_dates.array' => 'يجب أن تكون التواريخ في شكل قائمة.',
            'exception_dates.*.required' => 'كل تاريخ مطلوب.',
            'exception_dates.*.date' => 'صيغة التاريخ غير صحيحة.',
            'exception_dates.*.after_or_equal' => 'يجب أن يكون التاريخ اليوم أو بعده.',
            'reason.string' => 'سبب الإجازة يجب أن يكون نصًا.',
            'reason.max' => 'سبب الإجازة يجب ألا يتجاوز 255 حرفًا.',
        ];
    }
}
