<?php
namespace App\Services;

use App\Services\Interfaces\CarLocationServiceInterface;
use App\Repositories\Contracts\CarLocationRepositoryInterface;

class CarLocationService implements CarLocationServiceInterface
{
    protected $repo;

    public function __construct(CarLocationRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(array $data)
    {
        return $this->repo->create($data);
    }

    public function getLastLocation(int $carId)
    {
        return $this->repo->getLatestForCar($carId);
    }
      public function getLocationsForCar(int $carId)
    {
        return $this->repo->getLocationsForCar($carId);
    }

    public function getLastLocationsForActiveCars()
    {
        return $this->repo->getLastLocationsForActiveCars();
    }
}
