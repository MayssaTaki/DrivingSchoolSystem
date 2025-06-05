<?php
namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

//use App\Services\PasswordResetService;
use App\Http\Requests\SendResetRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Exceptions\InvalidResetTokenException;
use Illuminate\Http\JsonResponse;
use Exception;

class AuthController extends Controller
{
    protected $authService;
  //  protected PasswordResetService $service;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
       // $this->service = $service;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->userService->registerUser($data);

            return response()->json([
                'status' => 'success',
                'message' => 'تم تسجيل المستخدم بنجاح',
                'data' => $result
            ], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

   public function login(LoginRequest $request): JsonResponse
{
    try {
        $credentials = $request->validated();
        $result = $this->authService->login($credentials);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => $result
        ], 200);
    } catch (ThrottleRequestsException $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 429);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 401);
    }
}
    public function logout(): JsonResponse
    {
        $this->authService->logoutUser();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الخروج بنجاح'
        ], 200);
    }

    public function refreshToken(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->authService->refreshToken()
        ], 200);
    }

    public function sendResetCode(SendResetRequest $request): JsonResponse
    {
        $token = $this->service->sendResetLink($request->email);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إرسال رمز التحقق إلى البريد',
            'code' => $token // مؤقتًا لأغراض الاختبار
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->service->resetPassword($request->validated());

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
}
