<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StudentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
{
    $studentId = $this->route('student');
    
    if (is_string($studentId)) {
        $student = \App\Models\Student::with('user')->find($studentId);
        
        if (!$student) {
            throw ValidationException::withMessages([
                'student' => 'الطالب المطلوب غير موجود.'
            ]);
        }
        
        $this->route()->setParameter('student', $student);
    }
}

public function rules(): array
{
    $student = $this->route('student');
    
    if (!$student || !$student->user) {
        throw ValidationException::withMessages([
            'student' => 'بيانات المستخدم للطالب غير موجودة.'
        ]);
    }

    $rules = [
        'first_name' => 'sometimes|string|max:255',
        'last_name' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:users,email,' . $student->user->id,
        'password' => 'sometimes|min:6|confirmed',
        'phone_number' => 'sometimes|string|max:20|unique:students,phone_number',
        'date_of_Birth' =>'sometimes|date|before_or_equal:today',
        'gender' => 'sometimes|in:female,male',
        'address' => 'sometimes|string|max:100',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
    ];

    return $rules;
}
}
