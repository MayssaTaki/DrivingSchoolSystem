<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PracticalExamFailedNotification extends Notification
{
    public function __construct(public $schedule) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '❌ لم تنجح في الامتحان العملي',
            'body'  => "نأسف! لم تنجح في الامتحان العملي الذي كان بتاريخ {$this->schedule->exam_date} في الساعة {$this->schedule->exam_time}. يمكنك إعادة المحاولة لاحقًا.",
            'schedule_id' => $this->schedule->id,
            'exam_date' => $this->schedule->exam_date,
            'exam_time' => $this->schedule->exam_time,
        ];
    }
}
