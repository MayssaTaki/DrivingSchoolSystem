<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\StudentResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StudentUpdateRequest;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Requests\StudentRegisterRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Services\StudentService;


class StudentController extends Controller
{
    protected $studentService;

    public function __construct(studentService $studentService)
    {
        $this->studentService = $studentService;
    }





    public function register(StudentRegisterRequest $request): JsonResponse
{
    try {
        $data = $request->validated();
        $student = $this->studentService->register($data);

          return response()->json([
    'status' => 'success',
    'message' => 'تم تسجيل الطالب. تم إرسال رمز التحقق إلى بريدك الإلكتروني.',
    'data' => new StudentResource($student)
], 201);
    } catch (StudentRegistrationException | UserRegistrationException $e) {
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


public function getAllStudents(Request $request)
    {
        $name = $request->get('name');
        $students = $this->studentService->getAllStudents($name);

        if ($students->total() === 0) {
            return response()->json([
                'status' => 'fail',
                'message' => 'لم يتم العثور على طلاب',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم استرجاع الطلاب بنجاح',
            'data' => StudentResource::collection($students),
        ]);
    }

    public function destroy($id): JsonResponse
{
   

    try {
        $this->studentService->delete((int) $id);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الطالب والمستخدم المرتبط به بنجاح',
        ], Response::HTTP_OK);

    } catch (StudentNotFoundException $e) {
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

public function update(StudentUpdateRequest $request, Student $student): JsonResponse
{
    try {
        $updatedStudent = $this->studentService->update($student, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بيانات الطالب بنجاح.',
            'data' => new StudentResource($updatedStudent)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function countStudents(): JsonResponse
{
    try {
        $studentCount = $this->studentService->countStudents();
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب عدد الطلاب بنجاح.',
            'data' => [
                'student_count' => $studentCount
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
