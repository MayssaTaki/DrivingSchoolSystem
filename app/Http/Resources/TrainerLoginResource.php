<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerLoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray(Request $request): array
{
    if ($this->trainer) {
        return [
            'user_id' => $this->id,
            'first_name' => $this->trainer->first_name,
            'last_name' => $this->trainer->last_name,
            'date_of_Birth' => $this->trainer->date_of_Birth,
            'gender' => $this->trainer->gender,
            'email' => $this->user?->email,
            'name' => $this->user?->name,
            'image' => $this->trainer->image,
        ];
    }

    return [
        'user_id' => $this->id,
        'first_name' => $this->first_name,
        'last_name' => $this->last_name,
        'date_of_Birth' => $this->date_of_Birth,
        'gender' => $this->gender,
        'email' => $this->user?->email,
        'name' => $this->user?->name,
        'image' => $this->image,
    ];
}
}