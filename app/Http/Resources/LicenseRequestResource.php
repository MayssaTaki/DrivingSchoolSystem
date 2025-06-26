<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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

            // المستندات كمصفوفة URL
           'document_files' => collect($this->document_files)->map(function ($path) {
    $fullUrl = asset('storage/' . $path);
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $type = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'document';

    return [
        'url' => $fullUrl,
        'type' => $type,
        'name' => basename($path)
    ];
}),

            // معلومات الطالب
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->first_name . ' ' . $this->student->last_name,
                'email' => $this->student->user->email,
            ],

            // معلومات الرخصة
            'license' => [
                'code' => $this->license->code,
                'name' => $this->license->name,
            ]
        ];
    }
}
