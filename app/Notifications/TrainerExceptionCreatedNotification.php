<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TrainerExceptionCreatedNotification extends Notification
{
    public function __construct(public $trainer, public $count, public $reason) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '📆 طلب إجازة جديد',
            'body'  => "{$this->trainer->first_name} {$this->trainer->last_name} طلب إجازة لعدد {$this->count} يوم" . ($this->reason ? "، السبب: {$this->reason}" : ''),
            'trainer_id' => $this->trainer->id,
            'count' => $this->count,
            'reason' => $this->reason,
        ];
    }
}
