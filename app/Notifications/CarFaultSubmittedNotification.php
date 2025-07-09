<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CarFaultSubmittedNotification extends Notification
{
    public function __construct(public $fault) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '🚨 تم تسجيل عطل جديد',
            'body' => "تم تسجيل عطل للسيارة : {$this->fault->car->make}  {$this->fault->car->model}",
            'fault_id' => $this->fault->id,
            'car_id' => $this->fault->car_id,
        ];
    }
}
