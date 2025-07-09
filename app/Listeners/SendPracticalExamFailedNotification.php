<?php
namespace App\Listeners;

use App\Events\PracticalExamFailed;
use App\Notifications\PracticalExamFailedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPracticalExamFailedNotification implements ShouldQueue
{
    public function handle(PracticalExamFailed $event)
    {
        $schedule = $event->schedule;
        $licenseRequest = $schedule->licenseRequest; 
        $student = $licenseRequest?->student;      
        $user = $student?->user;

        if (!$user) {
            logger("⛔ لم يتم العثور على المستخدم المرتبط بالامتحان العملي ID: {$schedule->id}");
            return;
        }

        logger('📣 إرسال إشعار فشل الامتحان العملي للطالب:', [
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\PracticalExamFailedNotification::class)
            ->where('data->schedule_id', $schedule->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار الفشل مسبقًا للطالب ID: {$user->id} عن الجدول ID: {$schedule->id}");
            return;
        }

        $user->notify(new PracticalExamFailedNotification($schedule));

        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار فشل الامتحان...', [
                'to_user_id' => $user->id,
                'schedule_id' => $schedule->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                '❌ لم تنجح في الامتحان العملي',
                "نأسف! لم تنجح في الامتحان العملي الذي كان بتاريخ {$schedule->exam_date} في الساعة {$schedule->exam_time}. يمكنك إعادة المحاولة لاحقًا.",
                [
                    'schedule_id' => $schedule->id,
                    'exam_date' => $schedule->exam_date,
                    'exam_time' => $schedule->exam_time
                ]
            );
        }
    }
}
