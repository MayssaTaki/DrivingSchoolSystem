<?php

namespace App\Services;

use App\Models\Car;
use App\Repositories\Contracts\CarRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use App\Exceptions\CarNotFoundException;
use App\Events\ImageUploaded;

class CarService
{
    
    protected LogService $logService;
    protected ActivityLoggerService $activityLogger;

    public function __construct(
       
        CarRepositoryInterface $carRepository,LogService $logService
        ,        ActivityLoggerService $activityLogger,

        
    ) {
        $this->carRepository = $carRepository;
        $this->logService = $logService;
        $this->activityLogger = $activityLogger;


        
    }

  

    public function getAllcars(?string $make)
    {
        return $this->carRepository->getAllCars($make);
    }
    public function clearCarCache(): void
    {
        $this->carRepository->clearCache();
    }

    public function countCars(): int
    {
        return $this->carRepository->countCars();
    }

    public function add(array $data): Car
    {

        try {
            if (Gate::denies('create', Car::class)) {
                throw new AuthorizationException('ليس لديك صلاحية اضافة سيارة.');
            }
            if (isset($data['image'])) {
                $data['image'] = $data['image']->store('ImageCars', 'public');
                                  $fullPath = storage_path("app/public/{$data['image']}");
    event(new ImageUploaded($fullPath));
            }
                $car = $this->carRepository->create([
                    'make' => $data['make'],
                    'model' => $data['model'],
                    'color' => $data['color'],
                    'year' => $data['year'],
                    'license_plate' => $data['license_plate'],
                     'transmission' => $data['transmission'],
                  'is_for_special_needs' => $data['is_for_special_needs'],

                    'image' => $data['image']?? null,
                ]);

                $this->activityLogger->log(
                    'تم اضافة سيارة جديد',
                    ['make' => $car->make ],
                    'cars',
                    $car,
                    auth()->user(),
                    'added'
                );

                $this->clearCarCache();

                return $car;
            
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل اضافة سيارة ', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);                   throw $e;


        }
    }

     public function delete(int $id): void
{
     
    $car = $this->carRepository->findById($id);
    if (!$car) {
        throw new CarNotFoundException();
    }

    try {
         if (Gate::denies('delete', Car::class)) {
                throw new AuthorizationException('ليس لديك صلاحية حذف سيارة.');
            }
        $this->carRepository->deleteById($car->id);

        $this->activityLogger->log(
            'تم حذف سيارة',
            ['make' => $car->make],
            'cars',
            $car,
            auth()->user(),
            'deleted'
        );

        $this->clearCarCache();
    } catch (\Exception $e) {
        $this->logService->log(
            'error', 
            'فشل حذف السيارة', 
            [
                'message' => $e->getMessage(),
                'car_id' => $id,
                'trace' => $e->getTraceAsString()
            ], 
            'car'
        );

        throw new \RuntimeException('فشل حذف السيارة. يرجى المحاولة لاحقًا.');
    }
}

 public function update(Car $car, array $data): Car
    {
        try {
             if (Gate::denies('update', Car::class)) {
                throw new AuthorizationException('ليس لديك صلاحية تعديل سيارة.');
            }
             if (isset($data['image'])) {
                $data['image'] = $data['image']->store('ImageCars', 'public');
                                 $fullPath = storage_path("app/public/{$data['image']}");
    event(new ImageUploaded($fullPath));
            }
            $updateData = [];
    
            if (isset($data['license_plate']) && $data['license_plate'] !== $car->license_plate) {
                $updateData['license_plate'] = $data['license_plate'];
            }
      if (isset($data['make']) && $data['make'] !== $car->make) {
                $updateData['make'] = $data['make'];
            }
              if (isset($data['model']) && $data['model'] !== $car->model) {
                $updateData['model'] = $data['model'];
            }
              if (isset($data['color']) && $data['color'] !== $car->color) {
                $updateData['color'] = $data['color'];
            }
              if (isset($data['year']) && $data['year'] !== $car->year) {
                $updateData['year'] = $data['year'];
            }
               if (isset($data['transmission']) && $data['transmission'] !== $car->transmission) {
                $updateData['transmission'] = $data['transmission'];
            }
               if (isset($data['is_for_special_needs']) && $data['is_for_special_needs'] !== $car->is_for_special_needs) {
                $updateData['is_for_special_needs'] = $data['is_for_special_needs'];
            }
            if (isset($data['image']) && $data['image'] !== $car->getRawOriginal('image')) {
    $updateData['image'] = $data['image'];
}

    
              $this->activityLogger->log(
            'تم تعديل سيارة',
            ['make' => $car->make],
            'cars',
            $car,
            auth()->user(),
            'updated'
        );
     $this->clearCarCache();
            $this->carRepository->update($car, $updateData);
    
            return $car->fresh();  
        } catch (\Exception $e) {
            $this->logService->log('error', 'فشل تحديث بيانات السيارة', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ], 'car');
            throw $e; 
        }
    }
}
