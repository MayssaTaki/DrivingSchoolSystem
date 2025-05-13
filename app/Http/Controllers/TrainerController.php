<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TrainerResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\TrainerUpdateRequest;
use App\Models\Trainer;
use Illuminate\Http\Request;
use App\Http\Requests\TrainerRegisterRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Services\TrainerService;
use App\Exceptions\TrainerNotFoundException;

use Illuminate\Auth\Access\AuthorizationException;



class TrainerController extends Controller
{
    protected $trainerService;

    public function __construct(trainerService $trainerService)
    {
        $this->trainerService = $trainerService;
    }





    public function register(TrainerRegisterRequest $request): JsonResponse
{
    try {
        $data = $request->validated();
        $trainer = $this->trainerService->register($data);

       return response()->json([
    'status' => 'success',
    'message' => 'تم تسجيل المدرب. تم إرسال رمز التحقق إلى بريدك الإلكتروني.',
    'data' => new TrainerResource($trainer)
], 201);

    } catch (TrainerRegistrationException | UserRegistrationException $e) {
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


public function getAllTrainers(Request $request)
    {
        $name = $request->get('name');
        $trainers = $this->trainerService->getAllTrainers($name);

        if ($trainers->total() === 0) {
            return response()->json([
                'status' => 'fail',
                'message' => 'لم يتم العثور على مدربين',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم استرجاع المدربين بنجاح',
            'data' => TrainerResource::collection($trainers),
        ]);
    }
    public function getAllTrainersApprove(Request $request)
    {
        $name = $request->get('name');
        $trainers = $this->trainerService->getAllTrainersApprove($name);

        if ($trainers->total() === 0) {
            return response()->json([
                'status' => 'fail',
                'message' => 'لم يتم العثور على مدربين',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم استرجاع المدربين بنجاح',
            'data' => TrainerResource::collection($trainers),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->trainerService->delete((int) $id);
    
            return response()->json([
                'status' => 'success',
                'message' => 'تم حذف المدرب والمستخدم المرتبط به بنجاح',
            ], Response::HTTP_OK);
    
        } catch (TrainerNotFoundException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
    
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'unauthorized',
                'message' => 'ليس لديك صلاحية لحذف هذا المدرب.',
            ], Response::HTTP_FORBIDDEN);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ غير متوقع أثناء الحذف',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }}

        public function update(TrainerUpdateRequest $request, Trainer $trainer): JsonResponse
        {
            try {
                $updatedTrainer = $this->trainerService->update($trainer, $request->validated());
        
                return response()->json([
                    'status' => 'success',
                    'message' => 'تم تحديث بيانات المدرب بنجاح.',
                    'data' => new TrainerResource($updatedTrainer)
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
        }
public function countTrainers(): JsonResponse
{
    try {
        $trainerCount = $this->trainerService->countTrainers();
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب عدد المدرب بنجاح.',
            'data' => [
                'trainer_count' => $trainerCount
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 403);
    }
}

public function approve($id): JsonResponse
{
    try {
        $trainer = $this->trainerService->approveTrainer($id);
        
        return response()->json([
            'success' => true,
            'message' => 'تمت الموافقة على حساب المدرب بنجاح',
          
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'فشل في الموافقة على المدرب: ' . $e->getMessage(),
            'data' => null
        ], $this->getStatusCode($e));
    }
}

private function getStatusCode(\Exception $e): int
{
    if ($e instanceof ModelNotFoundException) {
        return 404;
    }
    
    return 400;
}

public function reject($id): JsonResponse
{
    $this->trainerService->rejectTrainer($id);

    return response()->json([
        'message' => 'تم رفض حساب المدرب.'
    ]);
}

public function approved()
{
$trainers = $this->trainerService->getApprovedTrainers();
$count = $trainers->count();

if ($count > 0) {
    return response()->json([
        'message' => 'تم العثور على اللمدربين المقبولين.',
        'count' => $count,
    ], 200);
} else {
    return response()->json([
        'message' => 'لا يوجد مدربين مقبولون حتى الآن.',
        'count' => $count
    ], 200);
}
}

public function rejected()
{
$trainers = $this->trainerService->getRejectedTrainers();
$count = $trainers->count(); 

if ($count > 0) {
    return response()->json([
        'message' => 'تم العثور على المدربين المرفوضين.',
        'count' => $count,
    ], 200);
} else {
    return response()->json([
        'message' => 'لا يوجد مدربين مرفوضين حتى الآن.',
        'count' => $count
    ], 200);
}
}

public function pened()
{
$trainers = $this->trainerService->getPendingTrainers();
$count = $trainers->count();  

if ($count > 0) {
    return response()->json([
        'message' => 'تم العثور على المدربين المعلقين.',
        'count' => $count,
    ], 200);
} else {
    return response()->json([
        'message' => 'لا يوجد مدربين معلقين حتى الآن.',
        'count' => $count
    ], 200);
}
}

}
