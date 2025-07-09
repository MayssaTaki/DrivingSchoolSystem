<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class SessionAutoBookedNotification extends Notification
{
    public function __construct(public $booking) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '⚙️ تم حجز جلسة تدريب تلقائيًا',
            'body'  => "تم حجز جلسة تدريب بتاريخ {$this->booking->session->day_of_week} الساعة {$this->booking->session->start_time} تلقائيًا.",
            'booking_id' => $this->booking->id,
            'session_id' => $this->booking->session_id,
            'student_id' => $this->booking->student_id,
        ];
    }
}
