<?php
namespace App\Repositories\Contracts;
use App\Models\Car;

interface CarRepositoryInterface
{
    public function create(array $data): Car;
   public function getAllCars(?string $make, int $perPage = 10);
    public function clearCache();
    public function deleteById(int $id): bool;
    public function findById(int $id): ?Car;
    public function update(Car $car, array $data): Car;
    public function countCars(): int;
    public function isCarBook(int $carId): bool;
    public function updateStatus(int $carId, string $status): bool;
 public function isCarAvailable(int $carId): bool;
  public function findWithLock(int $id);
         public function find(int $id);
public function getAvailableCars();
public function getFirstAvailableForSession(string $date, string $time);



}