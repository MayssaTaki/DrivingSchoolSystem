<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CarAddedNotification extends Notification
{
    public function __construct(public $car) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '🚗تمت اضافة سيارة جديدة',
            'body' => " تم اضافة سيارة: {$this->car->make} {$this->car->model}",
            'car_id' => $this->car->id,
        ];
    }
}
