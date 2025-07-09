<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class SessionBookedNotification extends Notification
{
    public function __construct(public $booking) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '📅 تم حجز جلسة تدريب جديدة',
            'body'  => "تم حجز جلسة تدريب بتاريخ {$this->booking->session->day_of_week} الساعة {$this->booking->session->start_time}.",
            'booking_id' => $this->booking->id,
            'session_id' => $this->booking->session_id,
            'student_id' => $this->booking->student_id,
        ];
    }
}
