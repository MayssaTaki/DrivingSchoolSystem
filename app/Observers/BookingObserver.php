<?php

namespace App\Observers;
use Illuminate\Support\Facades\Auth;

use App\Models\Booking;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "updated" event.
     */
   public function updated(Booking $booking)
    {
        if ($booking->wasChanged('status')) {
            $booking->statusLogs()->create([
                'status'      => $booking->status,
                'changed_by'  => Auth::id(),
                'changed_at'  => now(),
            ]);
        }
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "restored" event.
     */
    public function restored(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "force deleted" event.
     */
    public function forceDeleted(Booking $booking): void
    {
        //
    }
}
