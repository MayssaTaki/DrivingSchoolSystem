<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class StudentRegisteredNotification extends Notification
{
    public function __construct(public $student) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'طالب جديد ',
            'body' => "{$this->student->first_name} {$this->student->last_name} سجل كطالب.",
            'student_id' => $this->student->id,
        ];
    }
}
