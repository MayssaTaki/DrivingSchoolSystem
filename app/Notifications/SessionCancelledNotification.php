<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class SessionCancelledNotification extends Notification
{
    public function __construct(
        public $booking,
        public $session,
        public bool $cancelledByStudent
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $who = $this->cancelledByStudent ? 'الطالب' : 'المدرب';
        return [
            'title' => '⚠️ تم إلغاء جلسة تدريب',
            'body'  => "قام {$who} بإلغاء جلسة التدريب بتاريخ {$this->session->session_date} الساعة {$this->session->start_time}.",
            'booking_id' => $this->booking->id,
            'session_id' => $this->session->id,
            'cancelled_by_student' => $this->cancelledByStudent,
        ];
    }
}
