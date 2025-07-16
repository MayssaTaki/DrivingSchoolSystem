<?php
namespace App\Repositories\Contracts;

interface ChatRepositoryInterface {
    public function createMessage(array $data);
    public function getUserConversations(int $userId);
    public function getMessagesByConversationId(int $conversationId);
        public function markMessageAsRead(int $messageId): bool;
    public function findOrCreateConversation(int $userId1, int $userId2);
public function countUnreadMessagesForUser(int $userId): int;

public function getUnreadCountsGroupedByConversation(int $userId): \Illuminate\Support\Collection;


}