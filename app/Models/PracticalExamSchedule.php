<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticalExamSchedule extends Model
{
    protected $fillable = ['license_request_id',
    'exam_time','exam_date','status','employee_id'];

    public function licenseRequest()
{
    return $this->belongsTo(LicenseRequest::class);
}

public function employee()
{
    return $this->belongsTo(Employee::class);
}

}
