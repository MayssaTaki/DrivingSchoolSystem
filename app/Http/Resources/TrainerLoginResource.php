<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerLoginResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $trainer = $this->trainer ?? $this;

        return [
               'id' => $this->user?->id,
            'email' => $this->user?->email,
        'name' => $this->user?->name,
            'trainer' => [
                'id' => $trainer->id,
        'user_id' => $this->user?->id,
                'first_name' => $trainer->first_name,
                'last_name' => $trainer->last_name,
                'phone_number' => $trainer->phone_number,
                'date_of_Birth' => $trainer->date_of_Birth,
                'address' => $trainer->address,
                'gender' => $trainer->gender,
                'image' => $trainer->image,
                'status' => $trainer->status,
                'license_number' => $trainer->license_number,
                'license_expiry_date' => $trainer->license_expiry_date,
                'experience' => $trainer->experience,
                'training_type' => $trainer->training_type,
                'created_at' => $trainer->created_at,
                'updated_at' => $trainer->updated_at,
            ]
        ];
    }
}
