<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use App\Traits\LogsActivity;
use App\Repositories\UserRepository;
use App\Repositories\StudentRepository;
use App\Services\TransactionService;
use App\Services\UserService;
use App\Exceptions\StudentRegistrationException;
use App\Exceptions\StudentNotFoundException;
use App\Exceptions\StudentUpdateException;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use App\Events\ImageUploaded;







class StudentService
{
    use LogsActivity;

    protected ActivityLoggerService $activityLogger;
    protected LogService $logService;
protected EmailVreificationService $emailservice;


    public function __construct(
        StudentRepositoryInterface $studentRepository,
        TransactionService $transactionService,
        UserService $userService,
        UserRepositoryInterface $userRepository,
        ActivityLoggerService $activityLogger,
        LogService $logService,
                EmailVerificationService $emailService,

        


    ) {
        $this->studentRepository = $studentRepository;
        $this->transactionService = $transactionService;
        $this->userService = $userService;
        $this->userRepository = $userRepository;
        $this->activityLogger = $activityLogger;
        $this->logService = $logService;
                $this->emailService=$emailService;



    }

    public function register(array $data): Student
    {
        try {
            return $this->transactionService->run(function () use ($data) {
                $data['role'] = 'student';
                $user = $this->userService->register($data);
                $data['user_id'] = $user->id;
                if (isset($data['image'])) {
                    $data['image'] = $data['image']->store('ImageStudents', 'public');
                            $fullPath = storage_path("app/public/{$data['image']}");
    event(new ImageUploaded($fullPath));
                }
   
                $studentData = [
                    'user_id' => $user->id,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone_number' => $data['phone_number'],
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                    'date_of_Birth'=>$data['date_of_Birth'],
                    'image' => $data['image'] ?? null,

                ];

                $student = $this->studentRepository->create($studentData);
                $this->emailService->sendVerificationCode($user);

                $this->activityLogger->log(
                    'تم تسجيل طالب جديد',
                    ['name' => $student->first_name . ' ' . $student->last_name],
                    'students',
                    $student, 
                    auth()->user(),
                    'created'
                );
                

                $this->clearstudentCache();

                return $student;
            });
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل تسجيل الطالب', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ], 'student');
            throw new StudentRegistrationException('فشل تسجيل الأستاذ والطالب : ' . $e->getMessage());
        }
    }

    public function getAllStudents(?string $name)
    {
        Gate::authorize('viewAny', Student::class);
        $result = $this->studentRepository->getAllStudents($name);

     

        return $result;
    }

    public function delete(int $id): void
    {
        try {
        $this->transactionService->run(function () use ($id) {
            $student = $this->studentRepository->findById($id);

            if (!$student) {
                throw new StudentNotFoundException('الطالب غير موجود.');
            }
            Gate::authorize('delete', $student);

            $this->studentRepository->deleteById($id);
            $this->userService->delete($student->user_id);

            $this->activityLogger->log(
                'تم حذف طالب',
                ['name' => $student->first_name . ' ' . $student->last_name],
                'students',
                $student,
                auth()->user(),
                'deleted'
            );

            $this->clearStudentCache();
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل حذف الطالب', [
            'message' => $e->getMessage(),
            'student_id' => $id,
            'trace' => $e->getTraceAsString()
        ], 'student');

        throw $e;
    }
    }

    public function update(Student $student, array $data): Student
{
    try {
        return $this->transactionService->run(function () use ($student, $data) {
           
          
            if (Gate::denies('update', $student)) {
               throw new AuthorizationException('ليس لديك صلاحية التعديل على المدرب.');
            }

            $user = $student->user;
            $userModifications = $this->userService->update($user, $data);
            
            if (isset($data['image'])) {
                $data['image'] = $data['image']->store('ImageStudents', 'public');
           $fullPath = storage_path("app/public/{$data['image']}");
    event(new ImageUploaded($fullPath));
            }

            $studentData = [
                'first_name' => $data['first_name'] ?? $student->first_name,
                'last_name' => $data['last_name'] ?? $student->last_name,
                'phone_number' => $data['phone_number'] ?? $student->phone_number,
                'address' => $data['address'] ?? $student->address,
                'gender' => $data['gender'] ?? $student->gender,
    'image' => $data['image'] ?? $student->getRawOriginal('image'), // هُنا استخدام القيمة الأصلية فقط
                'date_of_Birth' => $data['date_of_Birth'] ?? $student->date_of_Birth,
            ];

            $this->studentRepository->update($student, $studentData);

            $this->activityLogger->log(
                'تم تعديل بيانات المدرب',
                ['modified_properties' => $student->getChanges()],
                'students',
                $student,
                auth()->user(),
                'updated'
            );

            $this->clearStudentCache();

            return $student->fresh();
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل تحديث بيانات المدرب', [
            'message' => $e->getMessage(),
            'data' => $data,
            'trace' => $e->getTraceAsString()
        ], 'student');

        throw new studentUpdateException('حدث خطأ أثناء تحديث بيانات المدرب.');
    }
}

    public function clearStudentCache(): void
    {
        $this->studentRepository->clearCache();

       
    }

    public function countStudents(): int
    {
        Gate::authorize('count', Student::class);

        return $this->studentRepository->countStudents(); 
    }
}
