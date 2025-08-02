<?php
namespace App\Observers;

use App\Models\CarReservation;
use App\Models\CarReservationReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CarReservationObserver
{
    public function created(CarReservation $reservation)
    {
        $date = Carbon::parse($reservation->start_time)->toDateString();

        CarReservationReport::updateOrCreate(
            ['car_id' => $reservation->car_id, 'date' => $date],
            ['total_reservations' => DB::raw('total_reservations + 1')]
        );
    }

    public function deleted(CarReservation $reservation)
    {
        $date = Carbon::parse($reservation->start_time)->toDateString();

        $report = CarReservationReport::where('car_id', $reservation->car_id)
            ->where('date', $date)
            ->first();

        if ($report && $report->total_reservations > 0) {
            $report->decrement('total_reservations');
        }
    }
}
