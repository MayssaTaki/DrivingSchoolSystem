<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PracticalExamScheduledNotification extends Notification
{
    public function __construct(public $schedule) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '📅 تم جدولة امتحان عملي',
            'body'  => "تم تحديد موعد امتحانك العملي بتاريخ {$this->schedule->exam_date} في الساعة {$this->schedule->exam_time}. نتمنى لك التوفيق!",
            'schedule_id' => $this->schedule->id,
            'exam_date' => $this->schedule->exam_date,
            'exam_time' => $this->schedule->exam_time,
        ];
    }
}
