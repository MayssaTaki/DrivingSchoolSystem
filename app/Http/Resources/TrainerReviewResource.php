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

        $data = [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $this->student->first_name . ' ' . $this->student->last_name,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];

        if (in_array($user?->role, ['admin', 'employee'])) {
            $data['trainer_name'] = $this->trainer->first_name . ' ' . $this->trainer->last_name;
        }

        return $data;
    }
}
