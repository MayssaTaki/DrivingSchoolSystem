<?php
namespace App\Listeners;

use App\Events\SendMessage;
use App\Notifications\MessageNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMessageNotification implements ShouldQueue
{
    public function handle(SendMessage $event)
    {
        $message = $event->message;
     $conversation = $message->conversation;
$receiver = $conversation->receiver;


        $alreadyNotified = $receiver->notifications()
            ->where('type', \App\Notifications\MessageNotification::class)
            ->where('data->message_id', $message->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار مسبقًا للرسالة ID: {$message->id}");
             return;

        }

        $receiver->notify(new MessageNotification($message));
    }
}
