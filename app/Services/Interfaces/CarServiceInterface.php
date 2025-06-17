<?php

namespace App\Services\Interfaces;

use App\Models\Car;

interface CarServiceInterface
{
    /**
     * Get all cars optionally filtered by make.
     *
     * @param string|null $make
     * @return mixed
     */
    public function getAllcars(?string $make);

    /**
     * Clear car cache.
     *
     * @return void
     */
    public function clearCarCache(): void;

    /**
     * Count all cars.
     *
     * @return int
     */
    public function countCars(): int;

    /**
     * Add a new car.
     *
     * @param array $data
     * @return Car
     */
    public function add(array $data): Car;

    /**
     * Delete a car by its ID.
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;

    /**
     * Update the given car with the provided data.
     *
     * @param Car $car
     * @param array $data
     * @return Car
     */
    public function update(Car $car, array $data): Car;
}
