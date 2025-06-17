<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyResetCodeRequest;
use App\Services\Interfaces\PasswordResetServiceInterface;
use App\Exceptions\InvalidResetTokenException;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    protected PasswordResetServiceInterface $passwordResetService;

    public function __construct(PasswordResetServiceInterface $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

  
    public function sendResetCode(PasswordResetRequest $request): JsonResponse
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

   
    public function verifyResetCode(VerifyResetCodeRequest $request): JsonResponse
    {
        try {
            $this->passwordResetService->verifyCode($request->email, $request->code);

            return response()->json([
                'status' => 'success',
                'message' => 'تم التحقق من رمز التحقق بنجاح'
            ]);
        } catch (InvalidResetTokenException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ], 422);
        
        }
    }

   
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
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
        } 
    }

   
    public function resendResetCode(PasswordResetRequest $request): JsonResponse
    {
        try {
            $this->passwordResetService->resendCode($request->email);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إعادة إرسال رمز التحقق.'
            ]);
        } catch (\Exception $e) {
          return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
