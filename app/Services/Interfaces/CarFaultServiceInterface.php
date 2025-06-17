<?php

namespace App\Services\Interfaces;

interface CarFaultServiceInterface
{
    public function submitFault(array $data);

    public function getAllLatestFaults();

    public function getFaultsByTrainer($trainerId);

    public function clearfaultCache(): void;

    public function markCarAsInRepairByFault(int $faultId);

    public function markCarAsResolvedByFault(int $faultId);

    public function countFaultsPerCar();

    public function getTopFaultedCars($limit = 5);

    public function getMonthlyFaultsCount($year = null);

    public function getAverageMonthlyFaultsPerCar($year = null);

    public function getFaultsStatusCountPerCar();
}
