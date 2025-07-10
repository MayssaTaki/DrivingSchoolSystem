<?php
namespace App\Listeners;

use App\Events\TrainingScheduleActivated;
use App\Notifications\TrainingScheduleActivatedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTrainingScheduleActivatedNotification implements ShouldQueue
{
    public function handle(TrainingScheduleActivated $event)
    {
        $schedule = $event->schedule;
        $trainer = $schedule->trainer; 

        if (!$trainer || !$trainer->user) {
            logger("⛔ لم يتم العثور على المدرب أو المستخدم المرتبط بالجدول ID: {$schedule->id}");
            return;
        }

        $user = $trainer->user;

        logger('📣 إرسال إشعار تفعيل الجدول للمدرب:', [
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\TrainingScheduleActivatedNotification::class)
            ->where('data->schedule_id', $schedule->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار مسبقًا للمدرب ID: {$user->id} عن الجدول ID: {$schedule->id}");
            return;
        }

        $user->notify(new TrainingScheduleActivatedNotification($schedule));

        
    }
}
