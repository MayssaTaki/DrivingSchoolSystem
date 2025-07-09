<?php
namespace App\Events;

use App\Models\LicenseRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LicenseRequestRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(public LicenseRequest $licenseRequest, public string $reason) {}
}
