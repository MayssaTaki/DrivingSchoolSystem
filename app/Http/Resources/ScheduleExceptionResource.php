<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleExceptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'trainer_id' => $this->trainer_id,
            'exception_date' => $this->exception_date->format('Y-m-d'),
            'is_available' => $this->is_available,
            'available_start_time' => $this->available_start_time?->format('H:i'),
            'available_end_time' => $this->available_end_time?->format('H:i'),
            'reason' => $this->reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}