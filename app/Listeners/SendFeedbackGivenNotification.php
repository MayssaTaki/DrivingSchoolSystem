<?php
namespace App\Listeners;

use App\Events\FeedbackGiven;
use App\Notifications\FeedbackGivenNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendFeedbackGivenNotification implements ShouldQueue
{
    public function handle(FeedbackGiven $event)
    {
        $feedback = $event->feedback;
        $booking = $feedback->booking; 

        if (!$booking || !$booking->student || !$booking->student->user) {
            logger("⛔ لم يتم العثور على الطالب أو المستخدم المرتبط بالتقييم ID: {$feedback->id}");
            return;
        }

        $user = $booking->student->user;

        logger('📣 إرسال إشعار تقييم جديد للطالب:', [
            'user_id' => $user->id,
            'feedback_id' => $feedback->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\FeedbackGivenNotification::class)
            ->where('data->feedback_id', $feedback->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار مسبقًا للطالب ID: {$user->id} عن التقييم ID: {$feedback->id}");
            return;
        }

        $user->notify(new FeedbackGivenNotification($feedback));

       
    }
}
