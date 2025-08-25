<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmRefundRequest extends FormRequest
{
    public function rules(): array
    {
        return [
          'baseInvoice'   => 'required|numeric|exists:payment_transactions,invoice_id',
            'refundInvoice' => 'required|numeric',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
