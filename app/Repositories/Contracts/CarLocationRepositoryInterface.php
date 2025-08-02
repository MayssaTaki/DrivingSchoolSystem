<?php
namespace App\Repositories\Contracts;

interface CarLocationRepositoryInterface
{
    public function create(array $data);
    public function getLatestForCar(int $carId);
    public function getLocationsForCar(int $carId);
    public function getLastLocationsForActiveCars();
}