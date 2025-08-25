<?php
namespace App\Services\Interfaces;

interface MtnPaymentClientServiceInterface
{
   public function createInvoice(int $amountSyp, int $ttl = 15);
public function initiatePayment(array $data);
public function confirmPayment(array $data);
    public function getMonthlyFinancialReport();
          public function exportReportMonthlyPayment();
          public function exportLicensePayments();
           public function getMonthlyLicensePayments();
               public function exportBookingPayments();
                public function getMonthlyBookingPayments();
                   public function getDailyFinancialReport();
                         public function exportReportdailyPayment();
            public function getdailyLicensePayments();
            public function exportLicensePaymentsdaily();  
             public function getdailyBookingPayments();
                 public function exportBookingPaymentsdaily();               
    public function activate(string $secret = null);
public function getInvoice(int $invoiceId, ?string $transaction = null);
}