<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TrainerSessionDayResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'date' => $this->date,
            'day_name' => \Carbon\Carbon::parse($this->date)->translatedFormat('l'), // اسم اليوم بالعربية
            'sessions' => $this->sessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'status' => $session->status,
                ];
            }),
        ];
    }
}
