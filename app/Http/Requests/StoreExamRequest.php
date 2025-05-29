<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => 'required|in:driving,traffic_rules,traffic_signs,mechanics,first_aid,special_conditions,accident_handling',
            'duration_minutes' => 'required|integer|min:1',
            'questions' => 'required|array|max:40',
            'questions.*.question_text' => 'required|string',
            'questions.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'questions.*.choices' => 'required|array|min:2',
            'questions.*.choices.*.text' => 'required|string',
            'questions.*.choices.*.is_correct' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'نوع الامتحان مطلوب.',
            'type.in' => 'نوع الامتحان يجب أن يكون أحد الأنواع التالية: القيادة، قوانين السير، إشارات المرور، الميكانيك، الإسعافات الأولية، القيادة في ظروف خاصة، أو التصرف في الحوادث.',

            'duration_minutes.required' => 'مدة الامتحان مطلوبة.',
            'duration_minutes.integer' => 'مدة الامتحان يجب أن تكون رقمًا صحيحًا.',
            'duration_minutes.min' => 'يجب أن تكون مدة الامتحان دقيقة واحدة على الأقل.',

            'questions.required' => 'الأسئلة مطلوبة.',
            'questions.array' => 'قائمة الأسئلة يجب أن تكون مصفوفة.',
            'questions.max' => 'لا يمكن أن يحتوي الامتحان على أكثر من 40 سؤالاً.',

            'questions.*.question_text.required' => 'نص السؤال مطلوب لكل سؤال.',
            'questions.*.image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'questions.*.image.mimes' => 'الصورة يجب أن تكون بصيغة: jpeg، png، jpg، gif.',
            'questions.*.image.max' => 'الحد الأقصى لحجم الصورة هو 2 ميغابايت.',

            'questions.*.choices.required' => 'يجب تحديد اختيارات لكل سؤال.',
            'questions.*.choices.array' => 'الاختيارات يجب أن تكون مصفوفة.',
            'questions.*.choices.min' => 'يجب أن يحتوي كل سؤال على خيارين على الأقل.',

            'questions.*.choices.*.text.required' => 'نص الخيار مطلوب.',
            'questions.*.choices.*.is_correct.required' => 'يجب تحديد ما إذا كان الخيار صحيحًا أم لا.',
            'questions.*.choices.*.is_correct.boolean' => 'قيمة الصحّة يجب أن تكون true أو false.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
