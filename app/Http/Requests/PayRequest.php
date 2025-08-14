<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'invoice_id' => 'required|string|exists:payment_transactions,invoice_id',
            'guid'       => 'required|string',
            'phone'      => 'required|string|min:10',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
