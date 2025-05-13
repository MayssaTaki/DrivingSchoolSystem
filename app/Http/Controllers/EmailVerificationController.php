<?php

namespace App\Http\Controllers;
use App\Services\EmailVerificationService;
use App\Http\Requests\VerifyEmailRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\ResendVerificationRequest;

class EmailVerificationController extends Controller
{
    protected EmailVerificationService $service;

    public function __construct(EmailVerificationService $service)
    {
        $this->service = $service;
    }

    public function send()
    {
        $this->service->sendVerificationCode(auth()->user());
        return response()->json(['status' => 'success', 'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.']);
    }

   public function verify(VerifyEmailRequest $request)
{
    try {
        $this->service->verifyCodeById($request->user_id, $request->code);
        return response()->json([
            'status' => 'success',
            'message' => 'تم تفعيل البريد الإلكتروني.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'fail',
            'message' => $e->getMessage()
        ], 422);
    }
}
public function resend(ResendVerificationRequest $request)
{
    
    try {
        $this->service->resendVerificationCodeById($request->user_id);
        return response()->json([
            'status' => 'success',
            'message' => 'تم إرسال رمز تحقق جديد إلى بريدك الإلكتروني.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'fail',
            'message' => $e->getMessage()
        ], 422);
    }
}

}
