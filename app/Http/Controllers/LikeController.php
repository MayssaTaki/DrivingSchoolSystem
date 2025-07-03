<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\LikeServiceInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\StudentResource;

class LikeController extends Controller
{
    public function __construct(protected LikeServiceInterface $likeService) {}

    public function toggle(int $postId): JsonResponse
    {
        $liked = $this->likeService->toggleLike($postId);

        return response()->json([
            'status' => 'success',
            'message' => $liked ? 'تم الإعجاب بالمنشور' : 'تم إزالة الإعجاب',
        ]);
    }
      public function studentsByPost(int $postId): JsonResponse
    {
        $students = $this->likeService->getStudentsWhoLiked($postId);

        return response()->json([
            'status' => 'success',
            'data' => StudentResource::collection($students),
        ]);
    }
}
