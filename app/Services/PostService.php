<?php
namespace App\Services;

use App\Services\Interfaces\PostServiceInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\PostFile;

use App\Services\Interfaces\TransactionServiceInterface;

class PostService implements PostServiceInterface
{
    protected PostRepositoryInterface $postRepo;
protected LogServiceInterface $logService;
    protected TransactionServiceInterface $transactionService;

    protected ActivityLoggerServiceInterface $activityLogger;
    public function __construct(PostRepositoryInterface $postRepo,LogServiceInterface $logService
        ,        ActivityLoggerServiceInterface $activityLogger,
                TransactionServiceInterface $transactionService
)
    {
        $this->postRepo = $postRepo;
         $this->logService = $logService;
        $this->activityLogger = $activityLogger;
                $this->transactionService = $transactionService;

    }

    public function listPosts(int $perPage = 10): LengthAwarePaginator
    {
        return $this->postRepo->paginateWithRelations($perPage);
    }

public function store(array $data, array $files)
{
    try {
        if (Gate::denies('create', Post::class)) {
            throw new AuthorizationException('ليس لديك صلاحية إنشاء بوست.');
        }

        $data['user_id'] = auth()->user()->employee->id;

        return $this->transactionService->run(function () use ($data, $files) {
            $storedFiles = [];

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $type = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf';
                $filename = Str::uuid() . '.' . $extension;
                $path = $file->storeAs('post_files', $filename, 'public');

                $storedFiles[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'type' => $type,
                ];
            }

            $post = $this->postRepo->createPost($data, $storedFiles);

            $this->activityLogger->log(
                'تم إضافة بوست جديد',
                ['title' => $post->title],
                'posts',
                $post,
                auth()->user(),
                'create_post'
            );

            return $post;

        }, function (\Throwable $e) use ($data) {
            $this->logService->log(
                'error',
                'فشل إنشاء بوست',
                [
                    'message' => $e->getMessage(),
                    'data' => $data,
                    'trace' => $e->getTraceAsString(),
                ],
                'posts'
            );

            throw $e;
        });

    } catch (\Exception $e) {
        throw $e;
    }
}

     public function update(int $id, array $data, array $files): Post
    {
        return $this->transactionService->run(function () use ($id, $data, $files) {
            $post = $this->postRepo->findById($id);

            if (Gate::denies('update', $post)) {
                throw new AuthorizationException('ليس لديك صلاحية تعديل هذا البوست.');
            }

            $post->update([
                'title' => $data['title'] ?? $post->title,
                'body' => $data['body'] ?? $post->body,
            ]);

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $type = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf';
                $filename = Str::uuid() . '.' . $extension;

                $path = $file->storeAs('post_files', $filename, 'public');

                PostFile::create([
                    'post_id' => $post->id,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'type' => $type,
                ]);
            }

            $this->activityLogger->log(
                'تم تعديل بوست',
                ['post_id' => $post->id],
                'posts',
                $post,
                auth()->user(),
                'update_post'
            );

            return $post;
        }, function (\Throwable $e) use ($id) {
            $this->logService->log('error', 'فشل تعديل بوست', [
                'post_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 'posts');
            throw $e;
        });
    }

    public function destroy(int $id): bool
    {
        return $this->transactionService->run(function () use ($id) {
            $post = $this->postRepo->findById($id);

            if (Gate::denies('delete', $post)) {
                throw new AuthorizationException('ليس لديك صلاحية حذف هذا البوست.');
            }

            foreach ($post->files as $file) {
                Storage::disk('public')->delete($file->path);
                $file->delete();
            }

            $result = $post->delete();

            $this->activityLogger->log(
                'تم حذف بوست',
                ['post_id' => $post->id],
                'posts',
                $post,
                auth()->user(),
                'delete_post'
            );

            return $result;
        }, function (\Throwable $e) use ($id) {
            $this->logService->log('error', 'فشل حذف بوست', [
                'post_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 'posts');
            throw $e;
        });
    }

    public function countPosts(): int
    {
        return $this->postRepo->countPosts();
    }
}