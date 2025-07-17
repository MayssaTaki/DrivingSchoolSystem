<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'start_lat' => $this->start_lat,
            'start_lng' => $this->start_lng,
            'end_lat' => $this->end_lat,
            'end_lng' => $this->end_lng,
            'polyline' => $this->polyline,
            'distance_in_meters' => $this->distance_in_meters,
            'duration_in_seconds' => $this->duration_in_seconds,
            'start_address' => $this->start_address,
            'end_address' => $this->end_address,
        ];
    }
}
