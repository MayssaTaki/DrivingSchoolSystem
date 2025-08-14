<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivateRequest;
use App\Http\Requests\InvoiceRequest;
use App\Http\Requests\PayRequest;
use App\Http\Requests\ConfirmPaymentRequest;
use App\Http\Requests\RefundRequest;
use App\Http\Requests\ConfirmRefundRequest;
use App\Services\Interfaces\PaymentServiceInterface;

class PaymentController extends Controller
{
    public function activate(ActivateRequest $req,PaymentServiceInterface $svc)
    {
        return response()->json(
            $svc->activateTerminal($req->public_key, $req->secret, $req->serial)
        );
    }

    public function invoice(InvoiceRequest $req, PaymentServiceInterface $svc)
    {
        $result = $svc->createInvoice($req->amount);
        return response()->json($result);
    }

    public function pay(PayRequest $req,PaymentServiceInterface $svc)
    {
        return response()->json(
            $svc->initiatePayment($req->invoice_id, $req->guid, $req->phone)
        );
    }

    public function confirm(ConfirmPaymentRequest $req, PaymentServiceInterface $svc)
    {
        return response()->json(
            $svc->confirmPayment($req->invoice_id, $req->guid, $req->otp)
        );
    }

    public function refund(RefundRequest $req, PaymentServiceInterface $svc)
    {
        return response()->json(
            $svc->initiateRefund($req->invoice_id, $req->guid)
        );
    }

    public function confirmRefund(ConfirmRefundRequest $req,PaymentServiceInterface $svc)
    {
        return response()->json(
            $svc->confirmRefund($req->invoice_id, $req->guid, $req->otp)
        );
    }
}
