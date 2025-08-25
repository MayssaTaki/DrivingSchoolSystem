<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'], 
            'ttl'    => ['nullable', 'integer', 'min:1'],  
        ];
    }
}
