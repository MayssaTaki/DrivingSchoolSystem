<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class LicenseRequestedNotification extends Notification
{
    public function __construct(public $student, public $license) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '📄 طلب رخصة جديد',
            'body'  => "{$this->student->first_name} {$this->student->last_name} قدم طلبًا للحصول على رخصة بالكود: {$this->license->code}",
            'student_id' => $this->student->id,
            'license_id' => $this->license->id,
            'license_code' => $this->license->code,
        ];
    }
}
