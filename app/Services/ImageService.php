<?php
namespace App\Services;
use App\Services\Interfaces\ImageServiceInterface;
use Cloudinary\Cloudinary;

class ImageService implements ImageServiceInterface
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
    }

   public function getSignedUrl(?string $publicId ,int $expiryMinutes = 30): ?string
{
    if (!$publicId) return null;

    $extension = pathinfo($publicId, PATHINFO_EXTENSION);

    return $this->cloudinary->uploadApi()->privateDownloadUrl(
        $publicId,
        $extension,
        [
            'type' => 'authenticated',
         'expires_at' => now()->addMinutes($expiryMinutes)->timestamp
        ]
    );
}

}
