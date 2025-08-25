<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model {
    protected $fillable = ['invoice_id','operation_number','amount','status','raw_response','guid'];

  public function licenseRequest()
    {
        return $this->hasOne(LicenseRequest::class);
    }
 public function booking()
    {
        return $this->hasOne(Booking::class);
    }
}
