<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = auth()->user();

        if ($this->status !== 'approved') {
            if (!in_array($user?->role, ['admin', 'employee'])) {
                if ($user?->role !== 'trainer' || $this->trainer_id !== $user->trainer->id) {
                    return [];
                }
            }
        }

        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
