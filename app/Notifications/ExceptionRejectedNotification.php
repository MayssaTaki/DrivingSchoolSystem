<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ExceptionRejectedNotification extends Notification
{
    public function __construct(public $exception) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '❌ تم رفض طلب الإجازة',
            'body'  => "تم رفض طلب إجازتك بتاريخ {$this->exception->exception_date}" . ($this->exception->reason ? "، السبب: {$this->exception->reason}" : ''),
            'exception_id' => $this->exception->id,
            'date' => $this->exception->exception_date,
        ];
    }
}
