<?php
namespace App\Repositories\Contracts;

interface BookingStatusLogRepositoryInterface
{
public function paginateStatusLogs(int $perPage = 10);
public function exportBookingStatusLogs(); 
public function exportBookingStatusLogsPdf();


}
