<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiateRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'invoiceId' => 'required|numeric|exists:payment_transactions,invoice_id',
        ];
    }
}
