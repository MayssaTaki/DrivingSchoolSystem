<?php

namespace App\Http\Controllers;
use App\Http\Requests\SearchUserRequest;
use App\Http\Requests\UpdateUserProfileRequest;

use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Exception;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }
    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        try {
            $updatedUser = $this->userService->update($user, $request->validated());
  
            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث بيانات المستخدم بنجاح.',
                'data' => $updatedUser
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()
            ], 500);
        }
    }
    
}
