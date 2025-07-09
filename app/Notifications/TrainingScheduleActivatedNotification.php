<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TrainingScheduleActivatedNotification extends Notification
{
    public function __construct(public $schedule) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '✅ تم تفعيل جدول التدريب',
            'body'  => "تم تفعيل جدول التدريب الخاص بك ليوم {$this->schedule->day_of_week} من {$this->schedule->start_time} حتى {$this->schedule->end_time}.",
            'schedule_id' => $this->schedule->id,
        ];
    }
}
