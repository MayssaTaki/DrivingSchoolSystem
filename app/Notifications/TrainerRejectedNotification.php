<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TrainerRejectedNotification extends Notification
{
    public function __construct(public $trainer) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'تم رفضك  كمدرب',
            'body' => "للاسف! تم رفضك كمدرب: {$this->trainer->first_name} {$this->trainer->last_name}",
            'trainer_id' => $this->trainer->id,
        ];
    }
}
