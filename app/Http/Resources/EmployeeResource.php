<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'hire_date' => $this->hire_date,
        'phone_number'=>$this->phone_number,
            'address'=>$this->address,
            'gender'=>$this->gender,

            'email' => $this->user?->email,
            'name'  => $this->user?->name,
        ];}
}
