<?php
namespace App\Listeners;

use App\Events\ReviewRejected;
use App\Notifications\ReviewRejectedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendReviewRejectedNotification implements ShouldQueue
{
    public function handle(ReviewRejected $event)
    {
        $review = $event->review;

        $student = $review->student; 
        if (!$student || !$student->user) {
            logger("⛔ لم يتم العثور على الطالب أو المستخدم المرتبط بالتقييم ID: {$review->id}");
            return;
        }

        $user = $student->user;

        logger('📣 إرسال إشعار رفض التقييم للطالب:', [
            'user_id' => $user->id,
            'review_id' => $review->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\ReviewRejectedNotification::class)
            ->where('data->review_id', $review->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار الرفض مسبقًا للطالب ID: {$user->id} عن التقييم ID: {$review->id}");
            return;
        }

        $user->notify(new ReviewRejectedNotification($review));

        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار رفض التقييم...', [
                'to_user_id' => $user->id,
                'review_id' => $review->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                '❌ تم رفض تقييمك',
                "تم رفض تقييمك للمدرب ID: {$review->trainer_id} بتقييم: {$review->rating}",
                ['review_id' => $review->id, 'trainer_id' => $review->trainer_id, 'rating' => $review->rating]
            );
        }
    }
}
