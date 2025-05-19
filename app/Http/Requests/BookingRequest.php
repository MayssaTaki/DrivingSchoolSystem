<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'student';
    }

    public function rules(): array
    {
        return [
            'session_id' => 'required|exists:training_sessions,id',
            'car_id' => 'required|exists:cars,id',
        ];
    }

    public function messages(): array
    {
        return [
            'session_id.required' => 'يجب اختيار الجلسة.',
            'session_id.exists' => 'الجلسة غير موجودة.',
            'car_id.required' => 'يجب اختيار السيارة.',
            'car_id.exists' => 'السيارة غير موجودة.',
        ];
    }
}
