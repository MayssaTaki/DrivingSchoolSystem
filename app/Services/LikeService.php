<?php
namespace App\Services;

use App\Services\Interfaces\LikeServiceInterface;
use App\Repositories\Contracts\LikeRepositoryInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Models\Post;
use App\Models\Student;


class LikeService implements LikeServiceInterface
{
    public function __construct(
        protected LikeRepositoryInterface $likeRepo,
        protected TransactionServiceInterface $transactionService,
        protected LogServiceInterface $logService,
        protected ActivityLoggerServiceInterface $activityLogger
    ) {}

    public function toggleLike(int $postId): bool
    {
        return $this->transactionService->run(function () use ($postId) {
            $student = auth()->user()->student;
            $post = Post::findOrFail($postId);

            $liked = $this->likeRepo->toggleLike($postId, $student->id);

            $action = $liked ? 'أُعجب بالمنشور' : 'أزال الإعجاب من المنشور';

            $this->activityLogger->log(
                $action,
                ['post_id' => $post->id],
                'likes',
                $post,
                auth()->user(),
                'toggle_like'
            );

            return $liked;
        }, function (\Throwable $e) use ($postId) {
            $this->logService->log('error', 'فشل في تبديل الإعجاب', [
                'post_id' => $postId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 'likes');

            throw $e;
        });
    }

      public function getStudentsWhoLiked(int $postId)
    {
        $studentIds = $this->likeRepo->getStudentIdsByPost($postId);
        return Student::whereIn('id', $studentIds)
                      ->with('user')
                      ->get();
    }
}
