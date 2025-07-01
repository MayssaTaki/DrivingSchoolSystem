<?php
namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\PostFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostRepository implements PostRepositoryInterface
{
    public function paginateWithRelations(int $perPage = 10): LengthAwarePaginator
    {
        return Post::with([
            'user.employee',  
            'files',           
            'likes',          
        ])
        ->withCount('likes') 
        ->paginate($perPage);
    }

  public function createPost(array $data, array $storedFiles): Post
{
    $post = Post::create([
        'user_id' => $data['user_id'],
        'title' => $data['title'],
        'body' => $data['body'] ?? null,
    ]);

    foreach ($storedFiles as $file) {
        PostFile::create([
            'post_id' => $post->id,
            'path' => $file['path'],
            'original_name' => $file['original_name'],
            'type' => $file['type'],
        ]);
    }

    return $post;
}
public function findById(int $id): Post
{
    return Post::with('files')->findOrFail($id);
}



public function updatePost(int $id, array $data): Post
    {
        $post = $this->findById($id);
        $post->update([
            'title' => $data['title'] ?? $post->title,
            'body' => $data['body'] ?? $post->body,
        ]);
        return $post;
    }

    public function deletePost(int $id): bool
    {
        $post = $this->findById($id);

        foreach ($post->files as $file) {
            Storage::disk('public')->delete($file->path);
            $file->delete();
        }

        return $post->delete();
    }
}
