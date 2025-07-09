<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PracticalExamMarkedAbsentNotification extends Notification
{
    public function __construct(public $schedule) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '⚠️ تم تسجيل غيابك عن الامتحان العملي',
            'body'  => "لقد تم تسجيل غيابك عن الامتحان العملي بتاريخ {$this->schedule->exam_date} في الساعة {$this->schedule->exam_time}.",
            'schedule_id' => $this->schedule->id,
            'exam_date' => $this->schedule->exam_date,
            'exam_time' => $this->schedule->exam_time,
        ];
    }
}
