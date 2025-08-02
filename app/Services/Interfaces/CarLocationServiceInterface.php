<?php
namespace App\Services\Interfaces;

interface CarLocationServiceInterface
{
    public function store(array $data);
    public function getLastLocation(int $carId);
     public function getLocationsForCar(int $carId);
    public function getLastLocationsForActiveCars();
}