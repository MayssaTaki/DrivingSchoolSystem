<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'trainer_id'=>$this->trainer_id,
            'exam_id' => $this->id,
            'title' => $this->title,
            'duration_minutes' => $this->duration_minutes,
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}
