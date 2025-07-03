<?php
namespace App\Repositories\Contracts;

interface LikeRepositoryInterface
{
    public function toggleLike(int $postId, int $studentId): bool;
        public function getStudentIdsByPost(int $postId);

}
