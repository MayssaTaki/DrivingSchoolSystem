<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'invoice_id' => 'required|string|exists:payment_transactions,invoice_id',
            'guid'       => 'required|string',
            'otp'        => 'required|string|min:4',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
