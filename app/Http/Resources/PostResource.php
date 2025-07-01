<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->employee->first_name . ' ' . $this->user->employee->last_name,
            ],
            'files' => $this->files->map(fn($file) => [
                'url' => asset('storage/' . $file->path),
                'type' => $file->type,
                'name' => $file->original_name,
            ]),
            'likes_count' => $this->likes_count ?? 0,
'liked_by_auth_user' => auth()->check() && auth()->user()->student
    ? $this->likedByUser(auth()->user()->student->id)
    : false,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
