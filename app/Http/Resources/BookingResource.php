<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->user->name ?? null,
            ],
            'trainer' => [
                'id' => $this->trainer->id,
                'name' => $this->trainer->user->name ?? null,
            ],
            'car' => [
                'id' => $this->car->id,
                'model' => $this->car->model,
                'transmission' => $this->car->transmission,
            ],
            'session' => [
                'id' => $this->session->id,
                'date' => $this->session->session_date,
                'start_time' => $this->session->start_time,
                'end_time' => $this->session->end_time,
            ],
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
