<?php
namespace App\Listeners;

use App\Events\TrainerApproved;
use App\Notifications\TrainerApprovedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTrainerApprovedNotification implements ShouldQueue
{
    public function handle(TrainerApproved $event)
    {
        $trainer = $event->trainer;
        $user = $trainer->user; 

        if (!$user) {
            logger("⛔ لا يوجد مستخدم مرتبط بالمدرب ID: {$trainer->id}");
            return;
        }

        logger('📣 إرسال إشعار قبول للمدرب:', [
            'trainer_id' => $trainer->id,
            'user_id' => $user->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\TrainerApprovedNotification::class)
            ->where('data->trainer_id', $trainer->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار القبول مسبقًا للمدرب ID: {$trainer->id}");
            return;
        }

        $user->notify(new TrainerApprovedNotification($trainer));

       
    }
}
