<?php
namespace App\Listeners;

use App\Events\TrainerReviewed;
use App\Notifications\TrainerReviewedNotification;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTrainerReviewedNotification implements ShouldQueue
{
    public function handle(TrainerReviewed $event)
    {
        $review = $event->review;

        $users = User::whereIn('role', ['admin', 'employee'])->get();

        foreach ($users as $user) {
            logger('📣 إرسال إشعار تقييم جديد للمدرب:', [
                'user_id' => $user->id,
                'review_id' => $review->id,
                'trainer_id' => $review->trainer_id,
                'fcm_token_exists' => !empty($user->fcm_token),
            ]);

            $alreadyNotified = $user->notifications()
                ->where('type', \App\Notifications\TrainerReviewedNotification::class)
                ->where('data->review_id', $review->id)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار مسبقًا للمستخدم ID: {$user->id} عن التقييم ID: {$review->id}");
                continue;
            }

            $user->notify(new TrainerReviewedNotification($review));

            if ($user->fcm_token) {
                logger('🚀 إرسال FCM إشعار تقييم جديد...', [
                    'to_user_id' => $user->id,
                    'review_id' => $review->id,
                ]);

                app(FirebaseService::class)->sendNotification(
                    $user->fcm_token,
                    '⭐ تقييم جديد للمدرب',
                    "تم إضافة تقييم جديد للمدرب ID: {$review->trainer_id} بتقييم: {$review->rating}",
                    ['review_id' => $review->id, 'trainer_id' => $review->trainer_id, 'rating' => $review->rating]
                );
            }
        }
    }
}
