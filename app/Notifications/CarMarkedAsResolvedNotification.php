<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CarMarkedAsResolvedNotification extends Notification
{
    public function __construct(public $fault, public $car) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '✅ السيارة أصبحت متاحة',
            'body'  => "تم الانتهاء من تصليح السيارة {$this->car->make} {$this->car->model} ",
            'fault_id' => $this->fault->id,
            'car_id' => $this->car->id,
        ];
    }
}
