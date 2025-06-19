<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'license_plate' => 'sometimes|string|size:7|unique:cars,license_plate,' . $this->route('car')->id,
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'transmission' => 'sometimes|in:automatic,manual',
            'is_for_special_needs' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'year.digits' => 'سنة الصنع يجب أن تتكون من 4 أرقام.',
            'license_plate.size' => 'رقم اللوحة يجب أن يتكون من 7 خانات.',
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $car = $this->route('car');

            if (!$car) {
                return; 
            }

            $originalSpecialNeeds = $car->is_for_special_needs;

            $changingTransmission = $this->filled('transmission');
            $newTransmission = $this->input('transmission');

            if ($originalSpecialNeeds && $changingTransmission && $newTransmission !== 'automatic') {
                $validator->errors()->add(
                    'transmission',
                    '❌ لا يمكن تعديل نوع ناقل الحركة إلى غير أوتوماتيك لأن السيارة مخصصة لذوي الاحتياجات الخاصة.'
                );
            }
        });
    }
}
