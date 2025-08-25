<?php

namespace App\Http\Controllers;

use App\Services\MtnPaymentClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MtnTestController extends Controller
{
    // 🇸🇦 تعليق: نقطة اختبار لتفعيل التيرمنال
    public function activate(MtnPaymentClient $mtn)
    {
        $secret = env('MTN_ACTIVATION_SECRET');
        $resp = $mtn->activate($secret);
        return response()->json($resp);
    }

    // 🇸🇦 تعليق: نقطة اختبار لإنشاء فاتورة
    public function createInvoice(MtnPaymentClient $mtn)
    {
        $invoiceId = random_int(100000, 999999);
        $amountMinUnits = 100 * 100; // 🇸🇦 مثال: 100 * 100 (ضرب القيمة بـ 100)
        $resp = $mtn->createInvoice($invoiceId, $amountMinUnits, 15);
        return response()->json(['invoiceId'=>$invoiceId] + $resp);
    }

    // 🇸🇦 تعليق: بدء الدفع عبر الهاتف (ستصلكم OTP للزبون)
    public function initiate(MtnPaymentClient $mtn, Request $r)
    {
        $invoiceId = (int)$r->input('invoice');
        $phone     = (string)$r->input('phone');
        $guid      = (string) Str::uuid();
        $resp = $mtn->initiatePayment($invoiceId, $phone, $guid);
        return response()->json(['invoiceId'=>$invoiceId,'guid'=>$guid] + $resp);
    }

    // 🇸🇦 تعليق: تأكيد الدفع عبر الهاتف (إرسال OTP مرمّز)
    public function confirm(MtnPaymentClient $mtn, Request $r)
    {
        $invoiceId = (int)$r->input('invoice');
        $phone     = (string)$r->input('phone');
        $guid      = (string)$r->input('guid');
        $op        = (int)$r->input('operationNumber');
        $otp       = (string)$r->input('otp');

        $resp = $mtn->confirmPayment($invoiceId, $phone, $guid, $op, $otp);
        return response()->json($resp);
    }
}
