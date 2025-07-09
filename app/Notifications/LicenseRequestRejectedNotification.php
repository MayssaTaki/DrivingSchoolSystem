<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class LicenseRequestRejectedNotification extends Notification
{
    public function __construct(public $licenseRequest, public $reason) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '❌ تم رفض طلب الرخصة',
            'body'  => "تم رفض طلبك للرخصة بالكود: {$this->licenseRequest->license->code}. السبب: {$this->reason}",
            'license_request_id' => $this->licenseRequest->id,
            'license_id' => $this->licenseRequest->license_id,
            'license_code' => $this->licenseRequest->license->code,
            'reason' => $this->reason,
        ];
    }
}
