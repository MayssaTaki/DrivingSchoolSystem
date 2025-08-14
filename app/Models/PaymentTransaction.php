<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model {
    protected $fillable = ['invoice_id','amount','status','raw_response','guid'];
}
