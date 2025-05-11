<?php
namespace App\Repositories\Contracts;
use App\Models\Trainer;

interface TrainerRepositoryInterface
{
    public function create(array $data): Trainer;
    public function getAllTrainers(?string $name, int $perPage = 10);
    public function clearCache();
    public function deleteById(int $id): bool;
    public function findById(int $id): ?Trainer;
    public function update(Trainer $trainer, array $data): Trainer;
    public function countTrainers(): int;
    public function approve(Trainer $trainer): Trainer;
    public function reject(Trainer $trainer): Trainer;
    public function getApprovedTrainers();
    public function getRejectedTrainers();
    public function getPendingTrainers();
    public function find($id): Trainer;



}