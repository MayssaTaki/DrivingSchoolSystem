<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return array_merge(
            $this->commonAttributes(),
            $this->shouldShowFullDetails() ? $this->adminOrEmployeeDetails() : $this->studentLimitedDetails()
        );
    }

    protected function commonAttributes(): array
    {
        return [
            'user_id'=>$this->user_id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'gender'     => $this->gender,
            'image' => $this->image,
            'email' => $this->user?->email,
            'name'  => $this->user?->name,
          'date_of_Birth' => $this->date_of_Birth,
'experience'=>$this->experience,
'training_type'=>$this->training_type,

        ];
    }

    protected function adminOrEmployeeDetails(): array
    {
        return [
            
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'license_number'=>$this->license_number,
            'license_expiry_date'=>$this->license_expiry_date,
            'status' => $this->status,
        ];
    }

    protected function studentLimitedDetails(): array
    {
        return []; 
    }

    protected function shouldShowFullDetails(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'employee']);
    }
}
