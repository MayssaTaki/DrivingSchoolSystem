<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowRandomQuestionsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'required|in:driving,traffic_rules,traffic_signs,mechanics,first_aid,special_conditions,accident_handling',
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'حقل النوع مطلوب.',
            'type.in' => 'نوع الامتحان غير صالح. يجب أن يكون أحد القيم التالية: قيادة، قواعد المرور، إشارات المرور، ميكانيكا، إسعافات أولية، شروط خاصة، التعامل مع الحوادث.',
        ];
    }
}
