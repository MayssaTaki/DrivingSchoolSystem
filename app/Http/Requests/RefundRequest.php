<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'invoice_id' => 'required|string|exists:payment_transactions,invoice_id',
            'guid'       => 'required|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
