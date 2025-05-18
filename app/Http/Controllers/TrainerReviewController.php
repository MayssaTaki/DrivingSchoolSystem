<?php

namespace App\Http\Controllers;
use App\Http\Requests\storeTrainerReviewRequest;
use App\Services\TrainerReviewService;

class TrainerReviewController extends Controller
{
    protected $service;

    public function __construct(TrainerReviewService $service)
    {
        $this->service = $service;
    }

    public function store(StoreTrainerReviewRequest $request)
    {
        $data = $request->validated();
        $data['student_id'] = auth()->user()->student->id;
        $review = $this->service->submitReview($data);

        return response()->json(['message' => 'تم إرسال التقييم بنجاح، بانتظار الموافقة.', 
        'data' => $review]);
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
}
