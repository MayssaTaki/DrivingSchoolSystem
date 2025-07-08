<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TrainerRegisteredNotification extends Notification
{
    public function __construct(public $trainer) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'مدرب جديد في الانتظار',
            'body' => "{$this->trainer->first_name} {$this->trainer->last_name} سجل كمدرب.",
            'trainer_id' => $this->trainer->id,
        ];
    }
}
