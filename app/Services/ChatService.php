<?php
namespace App\Services;

use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Events\MessageSent;
use App\Events\SendMessage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Services\Interfaces\ChatServiceInterface;
use Illuminate\Support\Str;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Services\Interfaces\LogServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;

class ChatService implements ChatServiceInterface
{
    protected ChatRepositoryInterface $chatRepo;
protected LogServiceInterface $logService;
    protected TransactionServiceInterface $transactionService;
protected FirebaseServiceInterface $firebaseservice;

    public function __construct(ChatRepositoryInterface $chatRepo
    ,LogServiceInterface $logService ,ActivityLoggerServiceInterface $activityLogger,
     TransactionServiceInterface $transactionService, FirebaseService $firebaseService) {
       
        $this->chatRepo = $chatRepo;
           $this->logService = $logService;
        $this->activityLogger = $activityLogger;
         $this->transactionService = $transactionService;
            $this->firebaseService = $firebaseService;
    }


public function getUserConversations(int $userId) {
    return $this->chatRepo->getUserConversations($userId);
}

public function sendMessageWithAttachment(int $senderId, int $receiverId, ?string $content, $file = null)
{
    try {
        return $this->transactionService->run(function () use ($senderId, $receiverId, $content, $file) {
            $type = 'text';

            if ($file) {
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = Str::uuid() . '.' . $extension;
                $path = $file->storeAs('chat_files', $filename, 'public');
                $content = $path;

                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $type = 'image';
                } else {
                    $type = 'file';
                }
            }

            $conversation = $this->chatRepo->findOrCreateConversation($senderId, $receiverId);

            $message = $this->chatRepo->createMessage([
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId,
                'content' => $content,
                'type' => $type,
            ]);
  
            $receiver = User::find($receiverId);
            if ($receiver && $receiver->fcm_token) {
                $this->firebaseService->sendNotification(
                    $receiver->fcm_token,
                    '💬 رسالة جديدة',
                     "{$message->sender->name}  لقد استلمت رسالة جديدة من : ",
                );
            }

            $this->activityLogger->log(
                'تم إرسال رسالة جديدة',
                ['message_id' => $message->id],
                'messages',
                $message,
                auth()->user(),
                'send_message'
            );

            event(new MessageSent($message));
            event(new SendMessage($message));


            return $message;

        }, function (\Throwable $e) use ($senderId, $receiverId, $content) {
            $this->logService->log(
                'error',
                'فشل إرسال الرسالة',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
                'messages'
            );

            throw $e;
        });

    } catch (\Exception $e) {
        throw $e;
    }
}

    public function getMessages(int $conversationId) {
        return $this->chatRepo->getMessagesByConversationId($conversationId);
    }
     public function markMessageAsRead(int $messageId): bool {
        return $this->chatRepo->markMessageAsRead($messageId);
    }

    public function countUnreadMessagesForUser(int $userId): int {
    return $this->chatRepo->countUnreadMessagesForUser($userId);
}

public function getUnreadCountsGroupedByConversation(int $userId): \Illuminate\Support\Collection {
    return $this->chatRepo->getUnreadCountsGroupedByConversation($userId);
}

}
