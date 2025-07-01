<?php
namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Post;
interface PostRepositoryInterface
{
    public function paginateWithRelations(int $perPage = 10): LengthAwarePaginator;
  public function createPost(array $data, array $storedFiles): Post;
public function updatePost(int $id, array $data): Post;
    public function deletePost(int $id): bool;
    public function findById(int $id): Post;

}
