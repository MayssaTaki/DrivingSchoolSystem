<?php
namespace App\Listeners;

use App\Events\PracticalExamScheduled;
use App\Notifications\PracticalExamScheduledNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPracticalExamScheduledNotification implements ShouldQueue
{
    public function handle(PracticalExamScheduled $event)
    {
        $schedule = $event->schedule;
        $licenseRequest = $schedule->licenseRequest; 
        $student = $licenseRequest?->student; 
        $user = $student?->user;

        if (!$user) {
            logger("⛔ لم يتم العثور على المستخدم المرتبط بالامتحان العملي ID: {$schedule->id}");
            return;
        }

        logger('📣 إرسال إشعار جدولة الامتحان العملي للطالب:', [
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\PracticalExamScheduledNotification::class)
            ->where('data->schedule_id', $schedule->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار مسبقًا للطالب ID: {$user->id} عن الجدول ID: {$schedule->id}");
            return;
        }

        $user->notify(new PracticalExamScheduledNotification($schedule));

        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار جدولة الامتحان...', [
                'to_user_id' => $user->id,
                'schedule_id' => $schedule->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                '📅 تم جدولة الامتحان العملي',
                "تم تحديد موعد امتحانك العملي بتاريخ {$schedule->exam_date} في الساعة {$schedule->exam_time}. نتمنى لك التوفيق!",
                [
                    'schedule_id' => $schedule->id,
                    'exam_date' => $schedule->exam_date,
                    'exam_time' => $schedule->exam_time
                ]
            );
        }
    }
}
