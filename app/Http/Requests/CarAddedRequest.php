<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CarAddedRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|string|digits:4',
            'color' => 'required|string|max:20',
            'license_plate' => 'required|string|size:7|unique:cars,license_plate',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'transmission' => 'required|in:automatic,manual',
            'is_for_special_needs' => 'required|boolean', 
        ];
    }

    public function messages()
    {
        return [
            'make.required' => 'نوع السيارة مطلوب.',
            'model.required' => 'موديل السيارة مطلوب.',
            'year.digits' => 'سنة الصنع يجب أن تتكون من 4 أرقام.',
            'color.required' => 'لون السيارة مطلوب.',
            'license_plate.required' => 'رقم اللوحة مطلوب.',
            'license_plate.unique' => 'رقم اللوحة مسجل مسبقًا.',
            'license_plate.size' => 'رقم اللوحة يجب أن يتكون من 7 خانات.',
            'image.image' => 'يجب أن يكون الملف صورة.',
            'image.mimes' => 'الصورة يجب أن تكون بصيغة: jpeg, png, jpg, gif.',
            'image.max' => 'الصورة يجب ألا تتجاوز 2 ميجابايت.',
            'transmission.required' => 'نوع ناقل الحركة مطلوب.',
            'transmission.in' => 'نوع ناقل الحركة يجب أن يكون أوتوماتيك أو عادي.',
            'is_for_special_needs.required' => 'حقل الاحتياجات الخاصة مطلوب.',
            'is_for_special_needs.boolean' => 'حقل الاحتياجات الخاصة يجب أن يكون نعم أو لا.',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'is_for_special_needs' => filter_var($this->is_for_special_needs, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (
                $this->boolean('is_for_special_needs') &&
                $this->input('transmission') !== 'automatic'
            ) {
                $validator->errors()->add(
                    'transmission',
                    '❌ إذا كانت السيارة مخصصة لذوي الاحتياجات الخاصة، يجب أن يكون نوع ناقل الحركة أوتوماتيك.'
                );
            }
        });
    }
}
