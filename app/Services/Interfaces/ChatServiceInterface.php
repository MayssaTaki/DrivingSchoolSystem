<?php

namespace App\Services\Interfaces;

interface ChatServiceInterface
{
    public function getMessages(int $conversationId);
    public function getUserConversations(int $userId);
        public function markMessageAsRead(int $messageId): bool;
    public function sendMessageWithAttachment(int $senderId, int $receiverId, ?string $content, $file = null);

    public function countUnreadMessagesForUser(int $userId): int;

public function getUnreadCountsGroupedByConversation(int $userId): \Illuminate\Support\Collection;

}