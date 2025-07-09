<?php
namespace App\Listeners;

use App\Events\TrainerRejected;
use App\Notifications\TrainerRejectedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTrainerRejectedNotification implements ShouldQueue
{
    public function handle(TrainerRejected $event)
    {
        $trainer = $event->trainer;
        $user = $trainer->user; 

        if (!$user) {
            logger("⛔ لا يوجد مستخدم مرتبط بالمدرب ID: {$trainer->id}");
            return;
        }

        logger('📣 إرسال إشعار رفض للمدرب:', [
            'trainer_id' => $trainer->id,
            'user_id' => $user->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\TrainerRejectedNotification::class)
            ->where('data->trainer_id', $trainer->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار الرفض مسبقًا للمدرب ID: {$trainer->id}");
            return;
        }

        $user->notify(new TrainerRejectedNotification($trainer));

        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار قبول...', [
                'to_user_id' => $user->id,
                'trainer_id' => $trainer->id
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                'للاسف! لم يتم قبولك',
                "للاسف{$trainer->first_name} {$trainer->last_name}, تم رفضك كمدرب.",
                ['trainer_id' => $trainer->id]
            );
        }
    }
}
