<?php
namespace App\Repositories;

use App\Repositories\Contracts\ChatRepositoryInterface ;
use App\Models\Conversation;
use App\Models\Message;

class ChatRepository implements ChatRepositoryInterface {
   
    public function createMessage(array $data) {
        return Message::create($data);
    }

    public function getMessagesByConversationId(int $conversationId) {
        return Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at')
            ->get();
    }

    public function findOrCreateConversation(int $userId1, int $userId2)
{
    $conversation = Conversation::where(function ($query) use ($userId1, $userId2) {
        $query->where('sender_id', $userId1)
              ->where('receiver_id', $userId2);
    })->orWhere(function ($query) use ($userId1, $userId2) {
        $query->where('sender_id', $userId2)
              ->where('receiver_id', $userId1);
    })->first();

    if (!$conversation) {
        if ($userId1 < $userId2) {
            $sender = $userId1;
            $receiver = $userId2;
        } else {
            $sender = $userId2;
            $receiver = $userId1;
        }

        $conversation = Conversation::create([
            'sender_id' => $sender,
            'receiver_id' => $receiver,
        ]);
    }

    return $conversation;
}


    public function getUserConversations(int $userId) {
    return Conversation::where('sender_id', $userId)
        ->orWhere('receiver_id', $userId)
        ->with(['messages' => function($q) {
            $q->latest()->limit(1);
        }, 'sender', 'receiver'])
        ->latest()
        ->get();
}

  public function markMessageAsRead(int $messageId): bool {
        return Message::where('id', $messageId)
            ->update(['is_read' => true]);
    }
}
