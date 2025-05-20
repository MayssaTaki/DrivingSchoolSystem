<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'license_plate' => $this->license_plate,
            'model'  => $this->model,
            'year' => $this->year,
        'make'=>$this->make,
        'color'=>$this->color,
            'image'=>$this->image,
            'transmission'=>$this->transmission,
           'is_for_special_needs' => (bool) $this->is_for_special_needs,     
          'display_type' => $this->car_type, 
                      'status' => $this->status,

        ];}
}
