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
            'trainer_id' => $this->id,
                        'user_id' => $this->user?->id,
            'first_name' => $this->trainer->first_name,
            'last_name' => $this->trainer->last_name,
    'phone_number' => $this->trainer->phone_number,
            'date_of_Birth' => $this->trainer->date_of_Birth,
            'gender' => $this->trainer->gender,
             'address' => $this->trainer->address,
'experience'=>$this->trainer->experience,
'training_type'=>$this->trainer->training_type,
 'license_number'=>$this->trainer->license_number,
 'license_expiry_date'=>$this->trainer->license_expiry_date,
            'email' => $this->user?->email,
            'name' => $this->user?->name,
            'image' => $this->trainer->image,
        ];
    }

    return [
        'trainer_id' => $this->id,
   'user_id' => $this->user?->id,
        'first_name' => $this->first_name,
        'last_name' => $this->last_name,
        'date_of_Birth' => $this->date_of_Birth,
        'gender' => $this->gender,
         'phone_number' => $this->phone_number,
            'address' => $this->address,
'experience'=>$this->experience,
'training_type'=>$this->training_type,
 'license_number'=>$this->license_number,
 'license_expiry_date'=>$this->license_expiry_date,
        'email' => $this->user?->email,
        'name' => $this->user?->name,
        'image' => $this->image,
    ];
}
}