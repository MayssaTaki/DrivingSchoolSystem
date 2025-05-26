<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\GiveFeedbackStudentRequest;
use App\Http\Resources\FeedbackStudentResource;
use App\Services\FeedbackStudentService;
use Illuminate\Http\JsonResponse;

class FeedbackStudentController extends Controller
{
    public function __construct(
        protected FeedbackStudentService $service
    ) {}

    public function store(GiveFeedbackStudentRequest $request): JsonResponse
    {
        $feedback = $this->service->giveFeedback($request->validated());

        return response()->json(new FeedbackStudentResource($feedback), 201);
    }
}
