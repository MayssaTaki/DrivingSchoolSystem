<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class EmployeeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    
    protected function prepareForValidation(): void
    {
        if (is_string($this->route('employee'))) {
            $this->route()->setParameter(
                'employee',
                \App\Models\Employee::findOrFail($this->route('employee'))
            );
        }

        
       
    }

    
    public function rules(): array
    {
        $employee = $this->route('employee');

        $rules = [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $employee->user_id,
            'password' => 'sometimes|min:6|confirmed',
            'phone_number' => 'sometimes|string|max:20|unique:employees,phone_number',
            'address' => 'sometimes|string|max:100',
            'hire_date'=>'sometimes|date|before_or_equal:today',
            'gender'=>'sometimes|in:female,male'
        ];

        

        return $rules;
    }
}
