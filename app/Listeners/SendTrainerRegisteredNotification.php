<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\TrainerRegistered;
use App\Notifications\TrainerRegisteredNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTrainerRegisteredNotification implements ShouldQueue
{
    public function handle(TrainerRegistered $event)
    {
        $trainerId = $event->trainer->id;

        logger('=== Listener وصل ===');

        $receivers = User::whereIn('role', ['employee', 'admin'])->get();
        logger('🔥 Listener Triggered داخل SendTrainerRegisteredNotification.php');

        foreach ($receivers as $user) {
            $alreadyNotified = $user->notifications()
                ->where('type', \App\Notifications\TrainerRegisteredNotification::class)
                ->where('data->trainer_id', $trainerId)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال الإشعار مسبقًا للمستخدم ID: {$user->id} عن المدرب ID: {$trainerId}");
                continue;
            }

            logger('📢 إرسال إشعار إلى:', [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'fcm_token_exists' => !empty($user->fcm_token),
            ]);

            $user->notify(new TrainerRegisteredNotification($event->trainer));

           
        }
    }
}
