<?php
namespace App\Services\Interfaces;
interface PaymentServiceInterface {
    public function activateTerminal(string $publicKey, string $secret, string $serial): array;
    public function createInvoice(int $amount): array;
    public function initiatePayment(string $invoiceId, string $guid, string $phone): array;
    public function confirmPayment(string $invoiceId, string $guid, string $otp): array;
    public function initiateRefund(string $invoiceId, string $guid): array;
    public function confirmRefund(string $invoiceId, string $guid, string $otp): array;
}
