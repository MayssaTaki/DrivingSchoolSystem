<?php

namespace App\Repositories;

use App\Models\PaymentTransaction;
use App\Repositories\Contracts\PaymentTransactionRepositoryInterface;

class PaymentTransactionRepository implements PaymentTransactionRepositoryInterface 
{
    public function create(array $data): PaymentTransaction
    {
        return PaymentTransaction::create($data);
    }

    public function updateStatus(string $invoiceId, string $status, ?array $rawResponse = null): bool
    {
        return PaymentTransaction::where('invoice_id', $invoiceId)
            ->update([
                'status'       => $status,
                'raw_response' => $rawResponse ? json_encode($rawResponse, JSON_UNESCAPED_UNICODE) : null,
            ]);
    }
 public function updateOperationNumber(string $invoiceId, string $operationNumber): bool
    {
        return PaymentTransaction::where('invoice_id', $invoiceId)
            ->update([
                'operation_number' => $operationNumber,
            ]);
    }
    public function findByInvoice(string $invoiceId): ?PaymentTransaction
    {
        return PaymentTransaction::where('invoice_id', $invoiceId)->first();
    }
}
