<?php
namespace App\Services;
use App\Services\Interfaces\LicenseServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use App\Models\License;
use App\Repositories\Contracts\LicenseRepositoryInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;

class LicenseService implements LicenseServiceInterface
{
protected LicenseRepositoryInterface $licenseRepository;
protected LogServiceInterface $logService;
protected ActivityLoggerServiceInterface $activityLogger;

    public function __construct(LicenseRepositoryInterface $licenseRepository
    ,LogServiceInterface $logService
        ,ActivityLoggerServiceInterface $activityLogger)
    {
        $this->licenseRepository = $licenseRepository;
         $this->logService = $logService;
        $this->activityLogger = $activityLogger;
    }

    public function listLicenses(?string $code = null)
{
    return $this->licenseRepository->getAllOrByCode($code);
}
public function createLicense(array $data)
{
    try {
    if (Gate::denies('create', License::class)) {
        throw new AuthorizationException('ليس لديك صلاحية اضافة شهادة.');
    }

    $license = $this->licenseRepository->create($data);
 $this->activityLogger->log(
                    'تم اضافة رخصة جديدة',
                    ['code' => $license->code ],
                    'license',
                    $license,
                    auth()->user(),
                    'added license'
                );
    $this->clearLicenseCache(); 

    return $license;
     } catch (\Exception $e) {
            $this->logService->log('error', 'فشل اضافة رخصة ', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);                   throw $e;
}}

public function updateLicense(int $id, array $data)
{
     try {
    $license = $this->licenseRepository->findOrFail($id);

    if (Gate::denies('update', $license)) {
        throw new AuthorizationException('ليس لديك صلاحية تعديل شهادة.');
    }

    $updated = $this->licenseRepository->update($id, $data);
 $this->activityLogger->log(
                    'تم تعديل رخصة جديدة',
                    ['code' => $license->code ],
                    'license',
                    $updated,
                    auth()->user(),
                    'updated license'
                );
    $this->clearLicenseCache(); 

    return $updated;
       } catch (\Exception $e) {
            $this->logService->log('error', 'فشل تعديل رخصة ', [
                'message' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);                   throw $e;
}}


public function clearLicenseCache(): void
    {
        $this->licenseRepository->clearCache();
    }

    public function countLicenses(): int
    {
        return $this->licenseRepository->countLicenses();
    }

}