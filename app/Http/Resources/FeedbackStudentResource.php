<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackStudentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'session_id' => $this->session_id,
            'student_id' => $this->student_id,
            'trainer_id' => $this->trainer_id,
            'rating' => $this->rating,
            'notes' => $this->notes,
            'number_session' => $this->number_session,
        ];
    }
}
