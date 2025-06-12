<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackStudentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
           'id' => $this->id,
            'booking_id' => $this->booking_id,
            'level' => $this->level,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
