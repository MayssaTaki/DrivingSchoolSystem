<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GiveFeedbackStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
'session_id' => 'required|exists:training_sessions,id',
            'student_id' => 'required|exists:students,id',
            'trainer_id' => 'required|exists:trainers,id',
            'rating' => 'required|integer|min:1|max:5',
            'notes' => 'nullable|string',
        ];
    }
}
