<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TrainerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->route('trainer'))) {
            $this->route()->setParameter(
                'trainer',
                \App\Models\Trainer::findOrFail($this->route('trainer'))
            );
        }

    }
    public function rules(): array
    {
        $trainer = $this->route('trainer');

        $rules = [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $trainer->user_id,
            'password' => 'sometimes|min:6|confirmed',
            'phone_number' => 'sometimes|string|max:20|unique:trainers,phone_number',
            'license_number'=> 'sometimes|string|max:50',
            'specialization'=>'sometimes|in:regular,special_needs',
            'experience'=>'sometimes|string|max:255',
            'gender'=>'sometimes|in:female,male',
            'address'=>'sometimes|string|max:100',
            'license_expiry_date'=>'sometimes|date|before_or_equal:today',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048', 


        ];

 

        return $rules;
    }
}
