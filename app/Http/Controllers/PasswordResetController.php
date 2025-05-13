<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use App\Exceptions\InvalidResetTokenException;

class PasswordResetController extends Controller
{
    protected PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function sendResetCode(PasswordResetRequest $request)
    {
        try {
            $this->passwordResetService->sendResetLink($request->email);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إرسال رمز التحقق إلى البريد'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $this->passwordResetService->resetPassword($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'تم إعادة تعيين كلمة المرور بنجاح'
            ]);
        } catch (InvalidResetTokenException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء إعادة تعيين كلمة المرور'
            ], 500);
        }
    }
}