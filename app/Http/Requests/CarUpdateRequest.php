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
            'year' => 'sometimes|string|digits:4',
            'color' => 'sometimes|string|max:20',
            'license_plate' => 'sometimes|string|max:255|unique:cars,license_plate',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'transmission' => 'sometimes|in:automatic,manual',
            'is_for_special_needs' => 'sometimes|boolean', 
        ];
    }

   public function messages()
    {
        return [
           
           'year.digits' => 'يجب أن يتكون رقم الهاتف من 4 أرقام فقط.',
            'license_plate.unique' => 'رقم اللوحة مسجل مسبقًا.',
            'image.image' => 'يجب أن يكون الملف صورة.',
            'image.mimes' => 'الصورة يجب أن تكون بصيغة: jpeg, png, jpg, gif.',
            'image.max' => 'الصورة يجب ألا تتجاوز 2 ميجابايت.',
            'transmission.in' => 'نوع ناقل الحركة يجب أن يكون أوتوماتيك أو عادي.',
            'is_for_special_needs.boolean' => 'حقل الاحتياجات الخاصة يجب أن يكون نعم أو لا.',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'is_for_special_needs' => filter_var($this->is_for_special_needs, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
 
}