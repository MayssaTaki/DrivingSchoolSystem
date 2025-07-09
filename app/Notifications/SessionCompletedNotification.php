<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class SessionCompletedNotification extends Notification
{
    public function __construct(public $booking) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '✅ تم إنهاء جلسة تدريب',
            'body'  => "تم إنهاء جلسة تدريب بتاريخ {$this->booking->session->day_of_week} الساعة {$this->booking->session->start_time} من قبل المدرب ID: {$this->booking->trainer_id}.",
            'booking_id' => $this->booking->id,
            'session_id' => $this->booking->session_id,
            'trainer_id' => $this->booking->trainer_id,
        ];
    }
}
