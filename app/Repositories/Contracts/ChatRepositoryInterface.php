<?php
namespace App\Repositories\Contracts;

interface ChatRepositoryInterface {
    public function createMessage(array $data);
    public function getUserConversations(int $userId);
    public function getMessagesByConversationId(int $conversationId);
        public function markMessageAsRead(int $messageId): bool;
    public function findOrCreateConversation(int $senderId, int $receiverId);
}