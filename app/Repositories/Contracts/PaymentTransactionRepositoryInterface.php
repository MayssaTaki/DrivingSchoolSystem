<?php
namespace App\Repositories\Contracts;
use App\Models\PaymentTransaction;

interface PaymentTransactionRepositoryInterface {
    public function create(array $data): PaymentTransaction;
    public function updateStatus(string $invoiceId, string $status, ?array $rawResponse = null): bool;
     public function findByInvoice(string $invoiceId): ?PaymentTransaction;

}