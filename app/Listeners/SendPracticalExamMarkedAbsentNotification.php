<?php
namespace App\Listeners;

use App\Events\PracticalExamMarkedAbsent;
use App\Notifications\PracticalExamMarkedAbsentNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPracticalExamMarkedAbsentNotification implements ShouldQueue
{
    public function handle(PracticalExamMarkedAbsent $event)
    {
        $schedule = $event->schedule;
        $licenseRequest = $schedule->licenseRequest; 
        $student = $licenseRequest?->student;      
        $user = $student?->user;

        if (!$user) {
            logger("⛔ لم يتم العثور على المستخدم المرتبط بالامتحان العملي ID: {$schedule->id}");
            return;
        }

        logger('📣 إرسال إشعار غياب الامتحان العملي للطالب:', [
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

     
        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\PracticalExamMarkedAbsentNotification::class)
            ->where('data->schedule_id', $schedule->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار الغياب مسبقًا للطالب ID: {$user->id} عن الجدول ID: {$schedule->id}");
            return;
        }

     
        $user->notify(new PracticalExamMarkedAbsentNotification($schedule));

        
        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار غياب الامتحان...', [
                'to_user_id' => $user->id,
                'schedule_id' => $schedule->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                '⚠️ تم تسجيل غيابك عن الامتحان العملي',
                "لقد تم تسجيل غيابك عن الامتحان العملي بتاريخ {$schedule->exam_date} في الساعة {$schedule->exam_time}.",
                [
                    'schedule_id' => $schedule->id,
                    'exam_date' => $schedule->exam_date,
                    'exam_time' => $schedule->exam_time
                ]
            );
        }
    }
}
