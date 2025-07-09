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

        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار تعطيل الجدول التدريبي...', [
                'to_user_id' => $user->id,
                'schedule_id' => $schedule->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                '⚠️ تم تعطيل جدول التدريب',
                "تم تعطيل جدول التدريب الخاص بك ليوم {$schedule->day_of_week} من {$schedule->start_time} حتى {$schedule->end_time}.",
                ['schedule_id' => $schedule->id]
            );
        }
    }
}
