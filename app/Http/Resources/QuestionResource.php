<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'question_id' => $this->id,
            'text' => $this->question_text,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'choices' => ChoiceResource::collection($this->whenLoaded('choices')),
        ];
    }
}
