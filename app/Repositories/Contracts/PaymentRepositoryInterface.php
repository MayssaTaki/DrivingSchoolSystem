<?php
namespace App\Repositories\Contracts;

interface PaymentRepositoryInterface {
    public function record(array $data);
    public function updateStatus(string $invoiceId, string $status);
}
