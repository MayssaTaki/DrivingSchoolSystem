<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetTrainerSessionsRequest extends FormRequest
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
    ];
}

public function messages()
{
    return [
        'trainer_id.required' => 'يرجى تحديد معرف المدرب (trainer_id).',
        'trainer_id.integer' => 'معرف المدرب يجب أن يكون رقماً صحيحاً.',
        'trainer_id.exists' => 'المدرب غير موجود.',
    ];
}
}
