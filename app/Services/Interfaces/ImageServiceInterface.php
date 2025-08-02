<?php
namespace App\Services\Interfaces;

interface ImageServiceInterface
{
   public function getSignedUrl(?string $publicId ,int $expiryMinutes = 10): ?string;
}
