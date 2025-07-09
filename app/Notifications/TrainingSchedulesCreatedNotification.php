<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TrainingSchedulesCreatedNotification extends Notification
{
    public function __construct(public $trainer, public $count) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '📅 جداول تدريب جديدة',
            'body'  => "{$this->trainer->first_name} {$this->trainer->last_name} أضاف {$this->count} جدول تدريب جديد.",
            'trainer_id' => $this->trainer->id,
            'count' => $this->count,
        ];
    }
}
