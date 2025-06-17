<?php

namespace App\Services\Interfaces;

interface BookingStatusLogServiceInterface
{
    public function getPaginatedStatusLogs(int $perPage = 10);

    public function exportBookingStatusLogs();

    public function exportBookingStatusLogsPdf();
}
