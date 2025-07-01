<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\PostServiceInterface;
use App\Http\Resources\PostResource;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;


class PostController extends Controller
{
    protected PostServiceInterface $posts;

    public function __construct(PostServiceInterface $posts)
    {
        $this->posts = $posts;
    }

    public function index(): JsonResponse
    {
        $posts = $this->posts->listPosts(10);

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }
     public function store(PostStoreRequest $request): JsonResponse
    {
        $post = $this->posts->store($request->validated(), $request->file('files', []));

        return response()->json([
            'success' => true,
            'message' => '✅ تم إنشاء المنشور بنجاح',
            'post_id' => $post->id,
        ], 201);
    }

     public function update(int $id, PostUpdateRequest $req): JsonResponse
    {
        $post = $this->posts->update($id, $req->validated(), $req->file('files', []));
        return response()->json(['success'=>true,'data'=>new PostResource($post)], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->posts->destroy($id);
        return response()->json(['success'=>true,'message'=>'تم الحذف بنجاح'], 200);
    }
}
