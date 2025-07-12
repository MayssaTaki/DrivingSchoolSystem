<?php
namespace App\Listeners;

use App\Events\MessageSent;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastMessageToSocket implements ShouldQueue
 {
    public function handle(MessageSent $event) {
        $message = $event->message;

        try {
            $client = new Client();
            $client->post('http://localhost:3000/broadcast', [
                'json' => [
                    'room' => 'conversation_'.$message->conversation_id,
                    'message' => [
                        'id' => $message->id,
                        'content' => $message->content,
                        'type' => $message->type,
                        'sender_id' => $message->sender_id,
                        'created_at' => $message->created_at->toDateTimeString(),
                    ]
                ]
            ]);
            logger('📢 Broadcasting message to socket once', ['message_id' => $message->id]);

        } catch (\Exception $e) {
            logger('⚠️ فشل إرسال الرسالة إلى سوكيت', ['error' => $e->getMessage()]);
        }
    }
}
