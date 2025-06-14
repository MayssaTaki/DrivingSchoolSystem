<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarFaultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'status' => $this->status,
            'created_at' => $this->created_at,
            
            'car' => [
                'make' => $this->car->make,
                'model' => $this->car->model,


            ],

            'trainer' => [
                'trainer_name' => $this->trainer->first_name . ' ' . $this->trainer->last_name,
            ],

           'booking' => $this->booking ? [
                'session_date' => optional($this->booking->session)->session_date,
            ] : null,
        ];
    }
}
