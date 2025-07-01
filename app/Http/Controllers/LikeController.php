<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\LikeServiceInterface;
use Illuminate\Http\JsonResponse;

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
}
