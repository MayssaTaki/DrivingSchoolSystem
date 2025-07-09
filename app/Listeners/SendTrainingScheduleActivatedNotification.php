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

        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار تفعيل الجدول...', [
                'to_user_id' => $user->id,
                'schedule_id' => $schedule->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                '✅ تم تفعيل جدول التدريب',
                "تم تفعيل جدول التدريب ليوم {$schedule->day_of_week} من {$schedule->start_time} حتى {$schedule->end_time}.",
                ['schedule_id' => $schedule->id]
            );
        }
    }
}
