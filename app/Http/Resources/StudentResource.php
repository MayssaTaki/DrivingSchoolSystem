<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = auth()->user(); 

        $data = [
                        'user_id'=>$this->user_id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'date_of_Birth' => $this->date_of_Birth,
            'gender'     => $this->gender,
            'email'          => $this->user?->email,
            'name'           => $this->user?->name,
            'image' => $this->image,

        ];

        

        return $data;
    }
}
