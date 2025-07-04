<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\GiveFeedbackStudentRequest;
use App\Http\Resources\FeedbackStudentResource;
use App\Services\Interfaces\FeedbackStudentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedbackStudentController extends Controller
{
    public function __construct(
        protected FeedbackStudentServiceInterface $service
    ) {}

    public function store(GiveFeedbackStudentRequest $request): JsonResponse
    {
        $feedback = $this->service->giveFeedback($request->validated());

        return response()->json(new FeedbackStudentResource($feedback), 201);
    }

    public function index()
    {
        $studentId = Auth::user()->student->id; 

        $feedbacks = $this->service->getStudentFeedbacks($studentId);

     return  FeedbackStudentResource::collection($feedbacks)->additional([
       
        'message' => 'تم جلب جميع التقييمات بنجاح.',
    ]);}
    public function getTrainerFeedbacks()
{
    $trainerId = Auth::user()->trainer->id;

    $feedbacks = $this->service->getTrainerFeedbacks($trainerId);

    return  FeedbackStudentResource::collection($feedbacks)->additional([
       
        'message' => 'تم جلب جميع التقييمات بنجاح.',
    ]);
}
public function getAllFeedbacks(Request $request): AnonymousResourceCollection
{
    $perPage = $request->get('per_page', 10); 

    $feedbacks = $this->service->getAllFeedbacksPaginated($perPage);

    return  FeedbackStudentResource::collection($feedbacks)->additional([
       
        'message' => 'تم جلب جميع التقييمات بنجاح.',
    ]);}
}
