<?php
namespace App\Listeners;

use App\Events\ExceptionRejected;
use App\Notifications\ExceptionRejectedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendExceptionRejectedNotification implements ShouldQueue
{
    public function handle(ExceptionRejected $event)
    {
        $exception = $event->exception;
        $trainer = $exception->trainer; 

        if (!$trainer || !$trainer->user) {
            logger("⛔ لم يتم العثور على المدرب أو المستخدم المرتبط بالإجازة ID: {$exception->id}");
            return;
        }

        $user = $trainer->user;

        logger('📣 إرسال إشعار رفض الإجازة للمدرب:', [
            'user_id' => $user->id,
            'exception_id' => $exception->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

       
        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\ExceptionRejectedNotification::class)
            ->where('data->exception_id', $exception->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار الرفض مسبقًا للمدرب ID: {$user->id} عن الإجازة ID: {$exception->id}");
            return;
        }

       
        $user->notify(new ExceptionRejectedNotification($exception));

        
        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار رفض الإجازة...', [
                'to_user_id' => $user->id,
                'exception_id' => $exception->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                '❌ تم رفض طلب الإجازة',
                "تم رفض طلب إجازتك بتاريخ {$exception->exception_date}" . ($exception->reason ? "، السبب: {$exception->reason}" : ''),
                ['exception_id' => $exception->id, 'date' => $exception->exception_date]
            );
        }
    }
}
