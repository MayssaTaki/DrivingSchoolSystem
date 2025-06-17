<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\EmployeeResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Requests\EmployeeRegisterRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Services\EmployeeService;
use App\Services\Interfaces\EmployeeServiceInterface;



class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeServiceInterface $employeeService)
    {
        $this->employeeService = $employeeService;
    }





    public function register(EmployeeRegisterRequest $request): JsonResponse
{
    try {
        $data = $request->validated();
        $employee = $this->employeeService->register($data);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الموظف والمستخدم بنجاح.',
            'data' => new EmployeeResource($employee)
        ], 201);
    } catch (EmployeeRegistrationException | UserRegistrationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'حدث خطأ غير متوقع.',
        ], 500);
    }
}


public function getAllEmployes(Request $request)
    {
        $name = $request->get('name');
        $employees = $this->employeeService->getAllEmployees($name);

        if ($employees->total() === 0) {
            return response()->json([
                'status' => 'fail',
                'message' => 'لم يتم العثور على موظفين',
                'data' => [],
            ], 404);
        }

        return EmployeeResource::collection($employees)->additional([
            'status' => 'success',
            'message' => 'تم استرجاع الموظفين بنجاح',
        ]);
    
    }

    public function destroy($id): JsonResponse
{
   

    try {
        $this->employeeService->delete((int) $id);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الموظف والمستخدم المرتبط به بنجاح',
        ], Response::HTTP_OK);

    } catch (EmployeeNotFoundException $e) {
        return response()->json([
            'status' => 'fail',
            'message' => $e->getMessage(),
        ], Response::HTTP_NOT_FOUND);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'حدث خطأ غير متوقع أثناء الحذف',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

public function update(EmployeeUpdateRequest $request, Employee $employee): JsonResponse
{
    try {
        $updatedEmployee = $this->employeeService->update($employee, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بيانات الموظف بنجاح.',
            'data' => new EmployeeResource($updatedEmployee)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function countEmployees(): JsonResponse
{
    try {
        $employeeCount = $this->employeeService->countEmployees();
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب عدد الموظف  بنجاح.',
            'data' => [
                'employee_count' => $employeeCount
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 403);
    }
}
}
