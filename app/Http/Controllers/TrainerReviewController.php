<?php

namespace App\Http\Controllers;
use App\Http\Requests\storeTrainerReviewRequest;
use App\Services\Interfaces\TrainerReviewServiceInterface;
use App\Http\Resources\TrainerReviewResource;
use Illuminate\Http\JsonResponse;

class TrainerReviewController extends Controller
{
    protected $service;

    public function __construct(TrainerReviewServiceInterface $service)
    {
        $this->service = $service;
    }

    public function store(StoreTrainerReviewRequest $request)
{
    $data = $request->validated();
    $data['student_id'] = auth()->user()->student->id;

    try {
        $review = $this->service->submitReview($data);

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال التقييم بنجاح، .',
            'data' => $review
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'فشل التقييم',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


    public function pending()
    {
        return response()->json($this->service->listPending());
    }

    public function approve($id)
    {
        $this->service->approveReview($id);
        return response()->json(['message' => 'تمت الموافقة على التقييم.']);
    }

    public function reject($id)
    {
        $this->service->rejectReview($id);
        return response()->json(['message' => 'تم رفض التقييم.']);
    }

      public function index(int $trainerId): JsonResponse
    {
        $reviews = $this->service->getTrainerReviews($trainerId);
        return response()->json(TrainerReviewResource::collection($reviews));
    }

     public function getPending(): JsonResponse
    {
        $reviews  = $this->service->getPendingReviews();

        return TrainerReviewResource::collection($reviews)
            ->additional([
                'status' => 'success',
                'message' => 'تم استرجاع التقييمات قيد الانتظار'
            ])
            ->response();
    }

    public function getApproved(): JsonResponse
    {
        $reviews  = $this->service->getApprovedReviews();

        return TrainerReviewResource::collection($reviews )
            ->additional([
                'status' => 'success',
                'message' => 'تم استرجاع التقييمات الموافق عليها'
            ])
            ->response();
    }

    public function getRejected(): JsonResponse
    {
       $reviews  = $this->service->getRejectedReviews();

        return TrainerReviewResource::collection($reviews )
            ->additional([
                'status' => 'success',
                'message' => 'تم استرجاع التقييمات المرفوضة'
            ])
            ->response();
    }

     public function topAndWorst(): JsonResponse
    {
        $top = $this->service->getTop5Trainers();
        $excludedIds = $top->pluck('trainer_id')->toArray();
            $worst = $this->service->getWorst5Trainers($excludedIds);

        return response()->json([
            'top_trainers' => $top,
            'worst_trainers' => $worst,
        ]);
    }
}
