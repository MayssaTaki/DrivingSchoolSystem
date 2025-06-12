<?php

namespace App\Http\Requests;
use App\Models\Booking;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class GiveFeedbackStudentRequest extends FormRequest
{
    public function withValidator(Validator $validator)
{
    $validator->after(function ($validator) {
        $booking = Booking::find($this->booking_id);

        if (!$booking) {
            $validator->errors()->add('booking_id', 'الحجز غير موجود.');
            return;
        }

       if ($booking->trainer_id !== auth()->user()->trainer->id) {
    $validator->errors()->add('booking_id', 'ليس لديك صلاحية لتقييم هذا الحجز.');
}

    });
}
    public function authorize(): bool
    {
        return true; 
    }

   public function rules(): array
    {
        return [
            'booking_id' => 'required|exists:bookings,id',
            'level' => 'required|in:beginner,intermediate,excellent',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'booking_id.required' => 'رقم الحجز مطلوب.',
            'booking_id.exists' => 'الحجز غير موجود.',
            'level.required' => 'مستوى التقييم مطلوب.',
            'level.in' => 'قيمة المستوى غير صحيحة.',
        ];
    }
}
