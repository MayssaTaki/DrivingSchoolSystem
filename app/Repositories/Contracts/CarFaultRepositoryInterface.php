<?php
namespace App\Repositories\Contracts;
use App\Models\CarFault;


interface CarFaultRepositoryInterface
{
    
    public function create(array $data);
public function getAllLatest();
public function getFaultsByTrainer($trainerId);
public function clearFaultsCache();
    public function countFaultsPerCar();
    public function getTopFaultedCars(int $limit = 5);
    public function getMonthlyFaultsCount(int $year = null);
    public function getAverageMonthlyFaultsPerCar(int $year = null);
    public function getFaultsStatusCountPerCar();
  public function isFaultProgress(int $faultId): bool;
       public function isFaultNew(int $faultId): bool;
public function findWithLock(int $faultId): CarFault;
public function updateStatus(int $faultId, string $status): bool;

}