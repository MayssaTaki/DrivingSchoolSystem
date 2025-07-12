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

    public function findOrCreateConversation(int $senderId, int $receiverId) {
        return Conversation::firstOrCreate(
            ['sender_id' => $senderId, 'receiver_id' => $receiverId]
        );
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
