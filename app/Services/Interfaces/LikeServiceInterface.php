<?php
namespace App\Services\Interfaces;

interface LikeServiceInterface
{
    public function toggleLike(int $postId): bool;
        public function getStudentsWhoLiked(int $postId);

}
