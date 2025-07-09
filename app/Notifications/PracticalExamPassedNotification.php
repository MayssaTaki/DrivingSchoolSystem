<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PracticalExamPassedNotification extends Notification
{
    public function __construct(public $schedule) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '🎉 تهانينا! لقد نجحت في الامتحان العملي',
            'body'  => "تم تسجيل نجاحك في الامتحان العملي بتاريخ {$this->schedule->exam_date} الساعة {$this->schedule->exam_time}.",
            'schedule_id' => $this->schedule->id,
            'exam_date' => $this->schedule->exam_date,
            'exam_time' => $this->schedule->exam_time,
        ];
    }
}
