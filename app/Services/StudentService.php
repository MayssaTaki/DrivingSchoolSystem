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
use App\Services\Interfaces\StudentServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Services\Interfaces\EmailVerificationServiceInterface;
use App\Services\Interfaces\UserServiceInterface;







class StudentService implements StudentServiceInterface
{

    protected ActivityLoggerServiceInterface $activityLogger;
    protected LogServiceInterface  $logService;
protected EmailVerificationServiceInterface $emailservice;
protected  $userRepository;
protected  $transactionService;
protected  $studentRepository;
protected  $userService;
protected  $emailService;


    public function __construct(
        StudentRepositoryInterface $studentRepository,
        TransactionServiceInterface $transactionService,
        UserServiceInterface $userService,
        UserRepositoryInterface $userRepository,
        ActivityLoggerServiceInterface $activityLogger,
        LogServiceInterface  $logService,
        EmailVerificationServiceInterface $emailService

        


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
                    'left_hand_disabled'=>$data['left_hand_disabled'],
                 'nationality'=>$data['nationality'] ?? 'syrian', 
                 'is_military'=>$data['is_military']
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
                

                $this->clearStudentCache();

                return $student;
            });
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل تسجيل الطالب', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ], 'student');
            throw new StudentRegistrationException('فشل تسجيل الطالب : ' . $e->getMessage());
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
                'nationality' => $data['nationality'] ?? $student->nationality,
                'gender' => $data['gender'] ?? $student->gender,
                'is_military' => $data['is_military'] ?? $student->is_military,
                'left_hand_disabled'=>$data['left_hand_disabled']??$student->left_hand_disabled,
    'image' => $data['image'] ?? $student->getRawOriginal('image'), 
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
