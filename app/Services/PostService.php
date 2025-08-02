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
use App\Models\User;
use App\Models\PostFile;
use App\Events\PostCreated;
use App\Services\Interfaces\TransactionServiceInterface;

class PostService implements PostServiceInterface
{
    protected PostRepositoryInterface $postRepo;
protected LogServiceInterface $logService;
    protected TransactionServiceInterface $transactionService;
protected FirebaseServiceInterface $firebaseservice;

    protected ActivityLoggerServiceInterface $activityLogger;
    public function __construct(PostRepositoryInterface $postRepo,LogServiceInterface $logService
        ,        ActivityLoggerServiceInterface $activityLogger,
                TransactionServiceInterface $transactionService,            FirebaseService $firebaseService

)
    {
        $this->postRepo = $postRepo;
         $this->logService = $logService;
        $this->activityLogger = $activityLogger;
                $this->transactionService = $transactionService;
                                    $this->firebaseService = $firebaseService;


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
                $extension = strtolower($file->getClientOriginalExtension());
                $type = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'document';
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $uniqueName = $originalName . '_' . Str::random(8);

                // رفع الملف إلى Cloudinary
                $uploaded = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::uploadApi()->upload(
                    $file->getRealPath(),
                    [
                        'public_id' => 'post_files/' . $uniqueName,
                        'quality' => 'auto:good',
                        'type' => 'authenticated',
                        'resource_type' => $extension === 'pdf' ? 'raw' : 'image'
                    ]
                );

                $storedFiles[] = [
                    'public_id' => $uploaded['public_id'],
                    'original_name' => $file->getClientOriginalName(),
                    'type' => $type,
                ];
            }

            $post = $this->postRepo->createPost($data, $storedFiles);

            event(new PostCreated($post));

            $students = User::where('role', 'student')
                ->whereNotNull('fcm_token')
                ->get();

            foreach ($students as $user) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    '📢 منشور جديد',
                    "تم نشر منشور جديد بعنوان: {$post->title}"
                );
            }

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

        // تحديث بيانات البوست
        $post->update([
            'title' => $data['title'] ?? $post->title,
            'body' => $data['body'] ?? $post->body,
        ]);

        // رفع الملفات الجديدة إلى Cloudinary
        foreach ($files as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            $type = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'document';
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $uniqueName = $originalName . '_' . Str::random(8);

            $uploaded = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::uploadApi()->upload(
                $file->getRealPath(),
                [
                    'public_id' => 'post_files/' . $uniqueName,
                    'quality' => 'auto:good',
                    'type' => 'authenticated',
                    'resource_type' => $extension === 'pdf' ? 'raw' : 'image'
                ]
            );

            PostFile::create([
                'post_id' => $post->id,
                'path' => $uploaded['public_id'],
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