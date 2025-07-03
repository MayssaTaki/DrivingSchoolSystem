<?php
namespace App\Services\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Post;
interface PostServiceInterface
{
    public function listPosts(int $perPage = 10): LengthAwarePaginator;
   public function store(array $data, array $files);
   public function update(int $id, array $data, array $files): Post;
    public function destroy(int $id): bool;
    public function countPosts(): int;

}
