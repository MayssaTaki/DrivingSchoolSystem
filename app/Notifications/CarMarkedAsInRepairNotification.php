<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CarMarkedAsInRepairNotification extends Notification
{
    public function __construct(public $fault, public $car) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '🚗 السيارة قيد الإصلاح',
            'body'  => "السيارة {$this->car->make} {$this->car->model} تم تحويلها لوضع الإصلاح بسبب العطل ",
            'fault_id' => $this->fault->id,
            'car_id' => $this->car->id,
        ];
    }
}
