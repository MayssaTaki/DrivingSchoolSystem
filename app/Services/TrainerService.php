<?php

namespace App\Services;

use App\Models\Trainer;
use Illuminate\Support\Facades\Hash;
use App\Traits\LogsActivity;
use App\Repositories\UserRepository;
use App\Repositories\TrainerRepository;
use App\Services\TransactionService;
use App\Services\EmailVreificationService;

use App\Services\UserService;
use App\Exceptions\TrainerRegistrationException;
use App\Exceptions\TrainerNotFoundException;
use App\Exceptions\TrainerUpdateException;
use App\Repositories\Contracts\TrainerRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Access\AuthorizationException;
use App\Events\ImageUploaded;






class TrainerService
{
    use LogsActivity;

    protected ActivityLoggerService $activityLogger;
    protected LogService $logService;
protected EmailVreificationService $emailservice;

    public function __construct(
        TrainerRepositoryInterface $trainerRepository,
        TransactionService $transactionService,
        UserService $userService,
        UserRepositoryInterface $userRepository,
        ActivityLoggerService $activityLogger,
        LogService $logService,
        EmailVerificationService $emailService,


        
    ) {
        $this->trainerRepository = $trainerRepository;
        $this->transactionService = $transactionService;
        $this->userService = $userService;
        $this->userRepository = $userRepository;
        $this->activityLogger = $activityLogger;
        $this->logService = $logService;
        $this->emailService=$emailService;


    }

    public function register(array $data): Trainer
    {
        try {
            return $this->transactionService->run(function () use ($data) {
                $data['role'] = 'trainer';
                $user = $this->userService->register($data);
                $data['user_id'] = $user->id;
              if (isset($data['image'])) {
    $data['image'] = $data['image']->store('ImageTrainers', 'public');

    $fullPath = storage_path("app/public/{$data['image']}");
    event(new ImageUploaded($fullPath));
}

                $trainerData = [
                    'user_id' => $user->id,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone_number' => $data['phone_number'],
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                    'image' => $data['image'] ?? null,
                    'date_of_Birth'=>$data['date_of_Birth'],

                   
                ];

                $trainer = $this->trainerRepository->create($trainerData);
                $this->emailService->sendVerificationCode($user);

                $this->activityLogger->log(
                    'تم تسجيل موظف جديد',
                    ['name' => $trainer->first_name . ' ' . $trainer->last_name],
                    'trainers',
                    $trainer, 
                    auth()->user(),
                    'created'
                );
                

                $this->cleartrainerCache();

                return $trainer;
            });
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل تسجيل المدرب', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ], 'trainer');
            throw new TrainerRegistrationException('فشل تسجيل الأستاذ واالمدرب : ' . $e->getMessage());
        }
    }

    public function getAllTrainers(?string $name)
    {
        $result = $this->trainerRepository->getAllTrainers($name);

     

        return $result;
    }

    public function getAllTrainersApprove(?string $name)
    {
        $result = $this->trainerRepository->getAllTrainersApprove($name);

     

        return $result;
    }
    public function delete(int $id): void
{
    try {
        $this->transactionService->run(function () use ($id) {
            $trainer = $this->trainerRepository->findById($id);

            if (!$trainer) {
                throw new TrainerNotFoundException('المدرب غير موجود.');
            }

            if (Gate::denies('delete', $trainer)) {
                throw new AuthorizationException('ليس لديك صلاحية حذف  المدرب.');
            }

            $this->trainerRepository->deleteById($id);
            $this->userService->delete($trainer->user_id);

            $this->activityLogger->log(
                'تم حذف مدرب',
                ['name' => $trainer->first_name . ' ' . $trainer->last_name],
                'trainers',
                $trainer,
                auth()->user(),
                'deleted'
            );

            $this->clearTrainerCache();
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل حذف المدرب', [
            'message' => $e->getMessage(),
            'trainer_id' => $id,
            'trace' => $e->getTraceAsString()
        ], 'trainer');

        throw $e;
    }
}


public function update(Trainer $trainer, array $data): Trainer
{
    try {
        return $this->transactionService->run(function () use ($trainer, $data) {
           
         
            if (Gate::denies('update', $trainer)) {
                throw new AuthorizationException('ليس لديك صلاحية التعديل على المدرب.');
            }

            $user = $trainer->user;
            $userModifications = $this->userService->update($user, $data);
            
                       if (isset($data['image'])) {
    $data['image'] = $data['image']->store('ImageTrainers', 'public');

    $fullPath = storage_path("app/public/{$data['image']}");
    event(new ImageUploaded($fullPath));
}
            $trainerData = [
                'first_name' => $data['first_name'] ?? $trainer->first_name,
                'last_name' => $data['last_name'] ?? $trainer->last_name,
                'phone_number' => $data['phone_number'] ?? $trainer->phone_number,
                'address' => $data['address'] ?? $trainer->address,
                'gender' => $data['gender'] ?? $trainer->gender,
                'image' => $data['image'] ?? $trainer->image,
                 'date_of_Birth' => $data['date_of_Birth'] ?? $student->date_of_Birth,
//
            ];

            $this->trainerRepository->update($trainer, $trainerData);

            $this->activityLogger->log(
                'تم تعديل بيانات المدرب',
                ['modified_properties' => $trainer->getChanges()],
                'trainers',
                $trainer,
                auth()->user(),
                'updated'
            );

            $this->clearTrainerCache();

            return $trainer->fresh();
        });
    } catch (\Exception $e) {
        $this->logService->log('error', 'فشل تحديث بيانات المدرب', [
            'message' => $e->getMessage(),
            'data' => $data,
            'trace' => $e->getTraceAsString()
        ], 'trainer');

        throw new TrainerUpdateException('حدث خطأ أثناء تحديث بيانات المدرب.');
    }
}
public function clearTrainerCache(): void
{
    $this->trainerRepository->clearCache();
}

    public function countTrainers(): int
    {
        return $this->trainerRepository->countTrainers(); 
    }

  public function approveTrainer($id): Trainer
{
    $trainer = $this->trainerRepository->find($id);
    
    try {
        if (Gate::denies('approve', $trainer)) {
            throw new AuthorizationException('ليس لديك صلاحية الموافقة على المدرب.');
        }

        $approvedTrainer = $this->trainerRepository->approve($trainer);

        $this->activityLogger->log(
            'تمت الموافقة على المدرب',
            ['trainer_id' => $trainer->id],
            'trainers',
            $trainer,
            auth()->user(),
            'approved'
        );

        $this->clearTrainerCache();

        return $approvedTrainer;
    } catch (\Exception $e) {
        throw new \Exception('فشل في الموافقة على المدرب: ' . $e->getMessage());
    }
}

public function rejectTrainer($id)
{
    $trainer = $this->trainerRepository->find($id);
    
    try {
        if (Gate::denies('reject', $trainer)) {
            throw new AuthorizationException('ليس لديك صلاحية رفض المدرب.');
        }

        $rejectedTrainer = $this->trainerRepository->reject($trainer);

        $this->activityLogger->log(
            'تم رفض المدرب',
            ['trainer_id' => $trainer->id],
            'trainers',
            $trainer,
            auth()->user(),
            'rejected'
        );

        $this->clearTrainerCache();

        return $rejectedTrainer;
    } catch (\Exception $e) {
        throw new \Exception('فشل في رفض المدرب: ' . $e->getMessage());
    }
}

    public function getApprovedTrainers()
    {
        return $this->trainerRepository->getApprovedTrainers();
    }
    public function getRejectedTrainers()
    {
        return $this->trainerRepository->getRejectedTrainers();
    }
    public function getPendingTrainers()
    {
        return $this->trainerRepository->getPendingTrainers();
    }
}
