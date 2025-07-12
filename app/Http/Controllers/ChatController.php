<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Services\Interfaces\ChatServiceInterface;
use Illuminate\Support\Str;
use App\Http\Requests\MarkMessageAsReadRequest;

class ChatController extends Controller {
    protected ChatServiceInterface $chatService;

    public function __construct(ChatServiceInterface $chatService) {
        $this->chatService = $chatService;
    }

   public function sendMessage(SendMessageRequest $request) {
    $senderId = auth()->id();
    $receiverId = $request->receiver_id;
    $content = $request->content;
    $file = $request->file('file');

    $message = $this->chatService->sendMessageWithAttachment($senderId, $receiverId, $content, $file);

    return response()->json([
        'message' => '✅ تم إرسال الرسالة بنجاح',
        'data' => $message
    ]);
}

    public function getMessages($conversationId) {
        $messages = $this->chatService->getMessages($conversationId);
        return response()->json(['data' => $messages]);
    }

    public function getUserConversations() {
    $userId = auth()->id();
    $conversations = $this->chatService->getUserConversations($userId);

    return response()->json([
        'message' => '✅ قائمة المحادثات',
        'data' => $conversations
    ]);
}

  public function markMessageAsRead(MarkMessageAsReadRequest $request) {
        $messageId = $request->message_id;

        $success = $this->chatService->markMessageAsRead($messageId);

        if ($success) {
            return response()->json([
                'status' => true,
                'message' => '✅ تم تعيين الرسالة كمقروءة بنجاح'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => '❌ فشل تعيين الرسالة كمقروءة'
        ], 400);
    }
}
