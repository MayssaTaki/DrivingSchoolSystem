<?php
namespace App\Services;

use App\Services\Interfaces\LikeServiceInterface;
use App\Repositories\Contracts\LikeRepositoryInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Models\Post;
use App\Models\Student;
use App\Events\PostLiked;
use App\Models\User;

class LikeService implements LikeServiceInterface
{     
    protected FirebaseServiceInterface $firebaseservice;

    public function __construct(
        protected LikeRepositoryInterface $likeRepo,
        protected TransactionServiceInterface $transactionService,
        protected LogServiceInterface $logService,
        protected ActivityLoggerServiceInterface $activityLogger,
                    FirebaseService $firebaseService

    ) {                    $this->firebaseService = $firebaseService;
}

    public function toggleLike(int $postId): bool
    {
        return $this->transactionService->run(function () use ($postId) {
            $student = auth()->user()->student;
            $post = Post::findOrFail($postId);

            $liked = $this->likeRepo->toggleLike($postId, $student->id);

            $action = $liked ? 'أُعجب بالمنشور' : 'أزال الإعجاب من المنشور';
if ($liked) {
    event(new PostLiked($post, $student));
    $users= User::where('role', 'employee')
                ->whereNotNull('fcm_token')
                ->get();

            foreach ($users as $user) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                 '❤️ إعجاب جديد بمنشور',
                    "{$student->user->name} أُعجب بمنشور ",
                );
            }
}

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
