<?php
namespace App\Listeners;

use App\Events\ReviewApproved;
use App\Notifications\ReviewApprovedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendReviewApprovedNotification implements ShouldQueue
{
    public function handle(ReviewApproved $event)
    {
        $review = $event->review;

        $student = $review->student; 
        if (!$student || !$student->user) {
            logger("⛔ لم يتم العثور على الطالب أو المستخدم المرتبط بالتقييم ID: {$review->id}");
            return;
        }

        $user = $student->user;

        logger('📣 إرسال إشعار قبول التقييم للطالب:', [
            'user_id' => $user->id,
            'review_id' => $review->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\ReviewApprovedNotification::class)
            ->where('data->review_id', $review->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار القبول مسبقًا للطالب ID: {$user->id} عن التقييم ID: {$review->id}");
            return;
        }

        $user->notify(new ReviewApprovedNotification($review));

        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار قبول التقييم...', [
                'to_user_id' => $user->id,
                'review_id' => $review->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                '✅ تم قبول تقييمك',
                "تمت الموافقة على تقييمك للمدرب ID: {$review->trainer_id} بتقييم: {$review->rating}",
                ['review_id' => $review->id, 'trainer_id' => $review->trainer_id, 'rating' => $review->rating]
            );
        }
    }
}
