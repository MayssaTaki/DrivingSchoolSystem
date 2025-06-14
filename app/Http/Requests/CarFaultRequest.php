<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarFaultRequest extends FormRequest
{
    public function authorize(): bool
    {

    return auth()->check() && auth()->user()->role === 'employee';


    }

    public function rules(): array
    {
        return [
            'fault_id' => 'required|exists:car_faults,id',
        ];
    }

    public function messages(): array
    {
        return [
            'fault_id.required' => 'رقم العطل مطلوب.',
            'fault_id.exists' => 'العطل المحدد غير موجود.',
        ];
    }
}
