<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->student ?? $this;

        return [
            'id' => $this->user?->id,
            'email' => $this->user?->email,
            'name' => $this->user?->name,
            'student' => [
                'id' => $student->id,
                'user_id' => $this->user?->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'phone_number' => $student->phone_number,
                'date_of_Birth' => $student->date_of_Birth,
                'gender' => $student->gender,
                'address' => $student->address,
                'image' => $student->image,
                 'left_hand_disabled' => $student->left_hand_disabled,
   'nationality' => $student->nationality,
    'is_military' => $student->is_military,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at,
            ]
        ];
    }
}
