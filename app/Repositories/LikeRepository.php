<?php
namespace App\Repositories;

use App\Models\Like;
use App\Repositories\Contracts\LikeRepositoryInterface;

class LikeRepository implements LikeRepositoryInterface
{
    public function toggleLike(int $postId, int $studentId): bool
    {
        $like = Like::where('post_id', $postId)->where('student_id', $studentId)->first();

        if ($like) {
            $like->delete();
            return false; 
        } else {
            Like::create([
                'post_id' => $postId,
                'student_id' => $studentId,
            ]);
            return true; 
        }
    }
    public function getStudentIdsByPost(int $postId)
    {
        return Like::where('post_id', $postId)
                   ->pluck('student_id');
    }
}
