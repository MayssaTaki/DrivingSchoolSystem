<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Exceptions\EmployeeRegistrationException;
use App\Exceptions\EmployeeNotFoundException;
use App\Exceptions\EmployeeUpdateException;

class EmployeeService
{
    protected EmployeeRepositoryInterface $employeeRepository;
    protected TransactionService $transactionService;
    protected UserService $userService;
    protected UserRepositoryInterface $userRepository;
    protected ActivityLoggerService $activityLogger;
    protected LogService $logService;

    public function __construct(
        EmployeeRepositoryInterface $employeeRepository,
        TransactionService $transactionService,
        UserService $userService,
        UserRepositoryInterface $userRepository,
        ActivityLoggerService $activityLogger,
        LogService $logService
    ) {
        $this->employeeRepository = $employeeRepository;
        $this->transactionService = $transactionService;
        $this->userService = $userService;
        $this->userRepository = $userRepository;
        $this->activityLogger = $activityLogger;
        $this->logService = $logService;
    }

    public function register(array $data): Employee
    {

        try {
            return $this->transactionService->run(function () use ($data) {
                $data['role'] = 'employee';
                $user = $this->userService->register($data);
                $data['user_id'] = $user->id;

                $employee = $this->employeeRepository->create([
                    'user_id' => $user->id,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'hire_date' => $data['hire_date'],
                    'phone_number' => $data['phone_number'],
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                ]);

                $this->activityLogger->log(
                    'تم تسجيل موظف جديد',
                    ['name' => $employee->first_name . ' ' . $employee->last_name],
                    'employees',
                    $employee,
                    auth()->user(),
                    'created'
                );

                $this->clearEmployeeCache();

                return $employee;
            });
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل تسجيل الموظف', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ], 'employee');

            throw new EmployeeRegistrationException('فشل تسجيل الأستاذ والموظف: ' . $e->getMessage());
        }
    }

    public function getAllEmployees(?string $name)
    {
        return $this->employeeRepository->getAllEmployees($name);
    }

    public function delete(int $id): void
    {
        $employee = $this->employeeRepository->findById($id);
        if (!$employee) {
            throw new EmployeeNotFoundException();
        }

        try {
            $this->transactionService->run(function () use ($employee) {
                $this->employeeRepository->deleteById($employee->id);
                $this->userService->delete($employee->user_id);

                $this->activityLogger->log(
                    'تم حذف موظف',
                    ['name' => $employee->first_name . ' ' . $employee->last_name],
                    'employees',
                    $employee,
                    auth()->user(),
                    'deleted'
                );

                $this->clearEmployeeCache();
            });
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل حذف الموظف', [
                'message' => $e->getMessage(),
                'employee_id' => $id,
                'trace' => $e->getTraceAsString()
            ], 'employee');

            throw $e;
        }
    }

    public function update(Employee $employee, array $data): Employee
    {
    
        try {
            return $this->transactionService->run(function () use ($employee, $data) {
                $user = $employee->user;
                $modifiedProperties = [];
    
                $userModifications = $this->userService->update($user, $data);
                $modifiedProperties = array_merge($modifiedProperties, $userModifications->getChanges()); 
                foreach (['first_name', 'last_name', 'address', 'gender', 'hire_date', 'phone_number'] as $field) {
                    if (isset($data[$field]) && $data[$field] !== $employee->$field) {
                        $modifiedProperties[$field] = [
                            'old' => $employee->getOriginal($field),
                            'new' => $data[$field]
                        ];
                        $employee->$field = $data[$field];
                    }
                }
    
                $updated = $this->employeeRepository->update($employee, $data);
    
                $this->activityLogger->log(
                    'تم تعديل بيانات الموظف',
                    ['modified_properties' => $modifiedProperties],
                    'employees',
                    $employee,
                    auth()->user(),
                    'updated'
                );
    
                $this->clearEmployeeCache();
    
                return $employee->fresh();  
            });
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل تحديث بيانات الموظف', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ], 'employee');
    
            throw new EmployeeUpdateException('حدث خطأ أثناء تحديث بيانات الموظف.');
        }
    }
    

    public function clearEmployeeCache(): void
    {
        $this->employeeRepository->clearCache();
    }

    public function countEmployees(): int
    {
        return $this->employeeRepository->countEmployees();
    }
}
