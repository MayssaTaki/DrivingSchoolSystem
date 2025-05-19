<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray(Request $request): array
{
    if ($this->student) {
        return [
            'user_id' => $this->id,
            'first_name' => $this->student->first_name,
            'last_name' => $this->student->last_name,
          'phone_number' => $this->student->phone_number,
            'date_of_Birth' => $this->student->date_of_Birth,
            'gender' => $this->student->gender,
            'email' => $this->email,
            'name' => $this->name,
            'image' => $this->student->image,
        ];
    }

    // إذا كان Student مباشرة
    return [
        'user_id' => $this->id,
        'first_name' => $this->first_name,
        'last_name' => $this->last_name,
        'date_of_Birth' => $this->date_of_Birth,
        'gender' => $this->gender,
    'phone_number' => $this->phone_number,

        'email' => $this->user?->email,
        'name' => $this->user?->name,
        'image' => $this->image,
    ];
}
}