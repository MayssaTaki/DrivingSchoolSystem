<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Casts\Json;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
   protected $fillable = ['code',
    'name','min_age','registration_fee','required_documents','requirements'];

     protected $casts = [
        'required_documents' => 'array', 
        'requirements' => 'array',
    ];

   public function licenseRequests()
{
    return $this->hasMany(LicenseRequest::class);
}

}
