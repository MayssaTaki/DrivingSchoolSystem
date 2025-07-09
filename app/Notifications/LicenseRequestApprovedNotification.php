<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class LicenseRequestApprovedNotification extends Notification
{
    public function __construct(public $licenseRequest) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '✅ تمت الموافقة على طلب الرخصة',
            'body'  => "تمت الموافقة على طلبك للرخصة بالكود: {$this->licenseRequest->license->code}",
            'license_request_id' => $this->licenseRequest->id,
            'license_id' => $this->licenseRequest->license_id,
            'license_code' => $this->licenseRequest->license->code,
        ];
    }
}
