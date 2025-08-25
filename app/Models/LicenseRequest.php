<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseRequest extends Model
{
   protected $fillable = [
        'student_id',
        'license_id',
        'status',
        'notes',
        'issued_at',
        'expires_at',
        'type',
        'payment_transaction_id',
        'document_files'
    ];
protected $casts = [
    'document_files' => 'array',
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }
    public function practicalExamSchedules()
{
    return $this->hasMany(PracticalExamSchedule::class);
}

 public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

}
