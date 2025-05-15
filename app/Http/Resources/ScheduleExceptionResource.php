<?php
namespace App\Http\Resources;
use Carbon\Carbon;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleExceptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            
            'exception_date' => $this->exception_date->format('Y-m-d'),
            'is_available' => $this->is_available,
            'available_start_time' => $this->available_start_time?->format('H:i'),
            'available_end_time' => $this->available_end_time?->format('H:i'),
            'reason' => $this->reason,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d'),
        ];
    }
}