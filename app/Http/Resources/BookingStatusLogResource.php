<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class BookingStatusLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'booking_id'   => $this->booking_id,
            'status'       => $this->status,
            'changed_at'   => Carbon::parse($this->changed_at)->format('Y-m-d H:i:s'),
            'changed_by'   => $this->changer?->name,
            'created_at'   => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),

          //  'name'         => $this->changer?->name,
            'role'         => $this->changer?->role,
        ];
    }
}
