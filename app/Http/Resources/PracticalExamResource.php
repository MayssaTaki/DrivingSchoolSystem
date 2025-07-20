<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PracticalExamResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'employee_id' => $this->employee_id,
            'exam_date' => $this->exam_date,
            'exam_time' => $this->exam_time,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'id' => $this->id,
            
'license_request' => new LicenseRequestResource($this->whenLoaded('licenseRequest')),
        ];
    }
}
