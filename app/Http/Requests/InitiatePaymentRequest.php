<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoiceId' => ['required', 'integer'],
'phone' => ['required', 'string', 'regex:/^9639[0-9]{8}$/'],
           
        ];
    }
}
