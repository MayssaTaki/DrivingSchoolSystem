<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'make' => 'sometimes|string|max:255',
            'model' => 'sometimes|string|max:255',
            'year' => 'sometimes|string',
            'color' => 'sometimes|string|max:20',
            'license_plate' => 'sometimes|string|max:255|unique:cars,license_plate',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'transmission' => 'sometimes|in:automatic,manual',
            'is_for_special_needs' => 'sometimes|boolean', 
        ];
    }

  

    public function prepareForValidation()
    {
        $this->merge([
            'is_for_special_needs' => filter_var($this->is_for_special_needs, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}