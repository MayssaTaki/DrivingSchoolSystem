<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class RouteDefinedNotification extends Notification
{
    public function __construct(public $route) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '📍 تم تحديد مسار التدريب',
            'body'  => "تم تحديد مسار التدريب لجلسة بتاريخ {$this->route->booking->session->session_date} الساعة {$this->route->booking->session->start_time}.",
            'route_id' => $this->route->id,
            'booking_id' => $this->route->booking_id,
        ];
    }
}
