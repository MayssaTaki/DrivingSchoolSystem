<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingStatusLog extends Model
{
    protected $fillable = ['booking_id', 'status', 'changed_at', 'changed_by'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
