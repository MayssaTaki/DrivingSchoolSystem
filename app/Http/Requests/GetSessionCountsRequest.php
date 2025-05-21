<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetSessionCountsRequest extends FormRequest
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
            'trainer_id' => ['required', 'integer', 'exists:trainers,id'],
            'month' => ['nullable', 'date_format:Y-m'], 
        ];
    }

    public function messages()
    {
        return [
            'trainer_id.required' => 'معرف المدرب مطلوب.',
            'trainer_id.integer' => 'معرف المدرب يجب أن يكون رقمًا صحيحًا.',
            'trainer_id.exists' => 'المدرب غير موجود.',
            'month.date_format' => 'صيغة الشهر يجب أن تكون YYYY-MM.',
        ];
    }
}
