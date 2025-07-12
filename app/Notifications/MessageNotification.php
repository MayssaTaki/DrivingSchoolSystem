<?php
namespace App\Notifications;

use App\Models\Message;
use Illuminate\Notifications\Notification;

class MessageNotification extends Notification
{
    public function __construct(public Message $message) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '📩 رسالة جديدة',
             'body'  => "لقد استلمت رسالة جديدة من :{$this->message->sender->name}",
            'sender_id' => $this->message->sender->name,
             'message_id' => $this->message->id,
        ];
    }
}
