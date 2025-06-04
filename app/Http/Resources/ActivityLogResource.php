<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray($request)
    {
        $causer = $this->causer;

        return [
                        'id' => $this->id,

            'log_name'    => $this->log_name,
            'description'    => $this->description,
            'event'       => $this->event,
            'created_at'  => $this->created_at,
            'user'        => $causer ? [
                
                'name'  => $causer->name,
                
                'role'  => $causer->role,
            ] : null,
            'properties'  => $this->properties ?? [],

        ];
    }
}
