<?php
namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
//use App\Services\PasswordResetService;
use App\Http\Requests\SendResetRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Exceptions\InvalidResetTokenException;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Http\Resources\StudentResource;
use App\Http\Resources\EmployeeResource;

use App\Http\Resources\TrainerLoginResource;


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

        // تحسين التحقق من البيانات
        $requiredKeys = ['user', 'token', 'role', 'refresh_token', 'expires_in'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $result)) {
                throw new Exception("المفتاح المطلوب غير موجود في النتيجة: {$key}");
            }
        }

        // استخدام match expression مع الأدوار الجديدة
        $userResource = match($result['role']) {
            'trainer' => new TrainerLoginResource($result['user']->trainer ?? $result['user']),
            'employee' => new EmployeeResource($result['user']->employee ?? $result['user']),
            'admin' => ($result['user']), // افترضنا وجود AdminResource
            'student' => new StudentResource($result['user']->student ?? $result['user']),
            default => throw new Exception("نوع المستخدم غير مدعوم: {$result['role']}")
        };

        // هيكل الرد الموحد
        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'user' => $userResource,
                'token' => $result['token'],
                'token_type' => $result['token_type'] ?? 'bearer',
                'refresh_token' => $result['refresh_token'],
                'expires_in' => $result['expires_in'],
                'role' => $result['role']
            ],
            'timestamp' => now()->toDateTimeString()
        ], 200);

    } catch (AuthenticationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'error_type' => 'authentication_error'
        ], 401);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'حدث خطأ أثناء تسجيل الدخول: ' . $e->getMessage(),
            'error_type' => 'server_error'
        ], 500);
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
