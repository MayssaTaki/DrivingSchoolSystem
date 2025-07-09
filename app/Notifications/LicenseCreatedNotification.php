<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class LicenseCreatedNotification extends Notification
{
    public function __construct(public $license) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '📄 تم إضافة رخصة جديدة',
            'body'  => "تمت إضافة رخصة جديدة : {$this->license->code}",
            'license_id' => $this->license->id,
            'code' => $this->license->code,
        ];
    }
}
