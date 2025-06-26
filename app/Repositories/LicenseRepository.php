<?php
namespace App\Repositories;
use App\Models\License;
use App\Repositories\Contracts\LicenseRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use App\Enums\LicenseType;

class LicenseRepository implements LicenseRepositoryInterface
{
  public function getAllOrByCode(?string $code = null)
{
    $cacheKey = 'licenses_' . ($code ?? 'all');

    return Cache::tags(['licenses'])->remember($cacheKey, now()->addMinutes(10), function () use ($code) {
        $query = License::query();

        if ($code) {
            $query->where('code', $code);
        }

        return $query->get();
    });
}

public function create(array $data)
{
    $code = $data['code'];
    $licenseType = LicenseType::tryFrom($code);

    if (!$licenseType) {
        throw new \InvalidArgumentException("نوع الرخصة غير معروف.");
    }

    return License::create([
        'code' => $code,
        'name' => $licenseType->label(), 
        'min_age' => $data['min_age'] ?? null,
        'registration_fee' => $data['registration_fee'],
        'required_documents' => $data['required_documents'],
        'requirements' => $data['requirements'] ?? [],
    ]);
}
public function update(int $id, array $data)
{
    $license = License::findOrFail($id);

    if (isset($data['code'])) {
        $licenseType = LicenseType::tryFrom($data['code']);
        if (!$licenseType) {
            throw new \InvalidArgumentException("نوع الرخصة غير معروف.");
        }

        $license->code = $data['code'];
        $license->name = $licenseType->label(); 
    }

    if (array_key_exists('min_age', $data)) {
        $license->min_age = $data['min_age'];
    }

    if (isset($data['registration_fee'])) {
        $license->registration_fee = $data['registration_fee'];
    }

    if (isset($data['required_documents'])) {
        $license->required_documents = $data['required_documents'];
    }

    if (array_key_exists('requirements', $data)) {
        $license->requirements = $data['requirements'];
    }

    $license->save();

    return $license;
}
public function findOrFail(int $id)
{
    return License::findOrFail($id);
}

 public function clearCache()
    {
        Cache::tags(['licenses'])->flush();
    }
  public function countLicenses(): int
    {
        return Cache::tags(['licenses'])->remember('licenses_count', now()->addMinutes(5), function () {
            return License::count();
        });
    }
}