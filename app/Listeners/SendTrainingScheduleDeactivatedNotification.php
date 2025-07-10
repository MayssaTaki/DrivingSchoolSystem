<?php
namespace App\Listeners;

use App\Events\TrainingScheduleDeactivated;
use App\Notifications\TrainingScheduleDeactivatedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTrainingScheduleDeactivatedNotification implements ShouldQueue
{
    public function handle(TrainingScheduleDeactivated $event)
    {
        $schedule = $event->schedule;
        $trainer = $schedule->trainer; 

        if (!$trainer || !$trainer->user) {
            logger("⛔ لم يتم العثور على المستخدم المرتبط بالمدرب أو الجدول ID: {$schedule->id}");
            return;
        }

        $user = $trainer->user;

        logger('📣 إرسال إشعار تعطيل الجدول التدريبي للمدرب:', [
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\TrainingScheduleDeactivatedNotification::class)
            ->where('data->schedule_id', $schedule->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار مسبقًا للمدرب ID: {$user->id} عن الجدول ID: {$schedule->id}");
            return;
        }

        $user->notify(new TrainingScheduleDeactivatedNotification($schedule));

      
    }
}
