<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\MtnPaymentClientServiceInterface;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Requests\InitiatePaymentRequest;
use App\Http\Requests\ConfirmPaymentRequest;
use App\Http\Requests\InitiateRefundRequest;
use App\Http\Requests\ConfirmRefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class MtnPaymentFlowController extends Controller
{
    public function __construct(protected MtnPaymentClientServiceInterface $mtn) {}

    public function createInvoice(CreateInvoiceRequest $request)
    {
        $resp = $this->mtn->createInvoice(
            $request->input('amount', 100),
            $request->input('ttl', 15)
        );

        return response()->json($resp);
    }

    public function initiatePayment(InitiatePaymentRequest $request)
    {
        $resp = $this->mtn->initiatePayment($request->validated());
        return response()->json($resp);
    }

    public function confirmPayment(ConfirmPaymentRequest $request)
    {
        $resp = $this->mtn->confirmPayment($request->validated());
        return response()->json($resp);
    }

 public function getInvoice(Request $request)
    {
        $request->validate([
            'invoiceId'   => 'required|integer',
            'transaction' => 'nullable|string'
        ]);

        $resp = $this->mtn->getInvoice(
            (int) $request->invoiceId,
            $request->transaction
        );

        return response()->json($resp);
    }


   public function initiateRefund(InitiateRefundRequest $request)
    {
        $resp = $this->mtn->initiateRefund($request->validated());

        return response()->json($resp);
    }

    public function confirmRefund(ConfirmRefundRequest $request)
    {
        $resp = $this->mtn->confirmRefund($request->validated());

        return response()->json($resp);
    }
    public function activate()
    {
        $resp = $this->mtn->activate();
        return response()->json($resp);
    }

    public function monthlyReport()
{
    $reports = $this->mtn->getMonthlyFinancialReport();

    return response()->json([
        'message' => 'التقرير المالي الشهري',
        'data' => $reports
    ]);
}
    public function DailyReport()
{
    $reports = $this->mtn->getDailyFinancialReport();

    return response()->json([
        'message' => 'التقرير المالي اليومي',
        'data' => $reports
    ]);
}
public function exportReportMonthlyPayment()
{
    return $this->mtn->exportReportMonthlyPayment();
}
public function exportReportdailyPayment()
{
    return $this->mtn->exportReportdailyPayment();
}
    public function monthlyReportLicenseRequest()
{
    $reports = $this->mtn->getMonthlyLicensePayments();

    return response()->json([
        'message' => 'التقرير المالي الشهري',
        'data' => $reports
    ]);
}
   public function dailyReportLicenseRequest()
{
    $reports = $this->mtn->getdailyLicensePayments();

    return response()->json([
        'message' => 'التقرير المالي اليومي',
        'data' => $reports
    ]);
}
public function exportMonthlyPaymentLicenseRequest()
{
    return $this->mtn->exportLicensePayments();
}
public function exportdailyPaymentLicenseRequest()
{
    return $this->mtn->exportLicensePaymentsdaily();
}
    public function monthlyReportBooking()
{
    $reports = $this->mtn->getMonthlyBookingPayments();

    return response()->json([
        'message' => 'التقرير المالي الشهري',
        'data' => $reports
    ]);
}
    public function DailyReportReportBooking()
{
    $reports = $this->mtn->getdailyBookingPayments();

    return response()->json([
        'message' => 'التقرير المالي اليومي',
        'data' => $reports
    ]);
}

public function exportMonthlyPaymentBooking()
{
    return $this->mtn->exportBookingPayments();
}
public function exportdailyPaymentBooking()
{
    return $this->mtn->exportBookingPaymentsdaily();
}
}