<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class FeedbackGivenNotification extends Notification
{
    public function __construct(public $feedback) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '📝 تقييم جديد من المدرب',
            'body'  => "تمت إضافة تقييم جديد لك بعد جلسة التدريب. المستوى: {$this->feedback->level}" . ($this->feedback->notes ? "، ملاحظات: {$this->feedback->notes}" : ''),
            'feedback_id' => $this->feedback->id,
            'level' => $this->feedback->level,
        ];
    }
}
