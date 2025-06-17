<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Traits\LogsActivity;
use App\Repositories\UserRepository;
use App\Exceptions\UserRegistrationException;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Interfaces\UserServiceInterface;
use App\Services\Interfaces\LogServiceInterface;


class UserService implements UserServiceInterface
{
    use LogsActivity;

    protected LogServiceInterface $logService;
    protected  $userRepository;


    public function __construct(UserRepositoryInterface $userRepository,
    LogServiceInterface $logService
    )
    {
        $this->userRepository = $userRepository;
        $this->logService = $logService;

    }

   
    public function register(array $data): User
    {
        try {
            $userData = [
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
            ];

            $user = $this->userRepository->create($userData);

           

            return $user;

        } catch (Exception $e) {
            $this->logService->log('error', 'فشل تسجيل المستخدم', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ], 'user');
            throw new UserRegistrationException('فشل إنشاء حساب المستخدم.');
        }
    }

   
    public function delete(int $id): void
    {
        try {
        $user = $this->userRepository->findByEmail($id);

        $this->userRepository->deleteById($id);
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل حذف المستخدم', [
            'message' => $e->getMessage(),
            'student_id' => $id,
            'trace' => $e->getTraceAsString()
        ], 'user');

        throw $e;
    }

      
    }

    public function update(User $user, array $data): User
    {
        try {
            $updateData = [];
    
           
    
            if (isset($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }
    
            if (isset($data['first_name']) || isset($data['last_name'])) {
                $first = $data['first_name'] ?? '';
                $last = $data['last_name'] ?? '';
                $updateData['name'] = trim($first . ' ' . $last);
            }
    
            $this->userRepository->update($user, $updateData);
    
            return $user->fresh();  
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل تحديث بيانات المستخدم', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ], 'user');
            throw $e; 
        }
    }
    
}
