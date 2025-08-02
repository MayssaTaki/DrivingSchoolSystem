<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Support\Str;
use App\Services\Interfaces\ImageServiceInterface;

class LicenseRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'type' => $this->type,
            'notes' => $this->notes,
            'issued_at' => $this->issued_at,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at->toDateTimeString(),

'document_files' => collect($this->document_files ?? [])->map(function ($publicId) {
    if (!$publicId) {
        return null;
    }
    $url = app(ImageServiceInterface::class)->getSignedUrl($publicId) ?? asset('images/default-doc.webp');
    $extension = strtolower(pathinfo($publicId, PATHINFO_EXTENSION));
    return [
        'url' => $url,
        'name' => basename($publicId)
    ];
})->filter()->values(),


            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->first_name . ' ' . $this->student->last_name,
                'email' => $this->student->user->email,
            ],

            'license' => [
                'code' => $this->license->code,
                'name' => $this->license->name,
            ]
        ];
    }
}
