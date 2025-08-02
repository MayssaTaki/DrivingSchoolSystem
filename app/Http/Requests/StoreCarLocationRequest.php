<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarLocationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'car_id'      => 'required|exists:cars,id',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'recorded_at' => 'nullable|date',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
