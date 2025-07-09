<?php
namespace App\Listeners;

use App\Events\ExceptionApproved;
use App\Notifications\ExceptionApprovedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendExceptionApprovedNotification implements ShouldQueue
{
    public function handle(ExceptionApproved $event)
    {
        $exception = $event->exception;
        $trainer = $exception->trainer; 

        if (!$trainer || !$trainer->user) {
            logger("⛔ لم يتم العثور على المدرب أو المستخدم المرتبط بالإجازة ID: {$exception->id}");
            return;
        }

        $user = $trainer->user;

        logger('📣 إرسال إشعار الموافقة على الإجازة:', [
            'user_id' => $user->id,
            'exception_id' => $exception->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\ExceptionApprovedNotification::class)
            ->where('data->exception_id', $exception->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار الموافقة مسبقًا للمدرب ID: {$user->id} عن الإجازة ID: {$exception->id}");
            return;
        }

        $user->notify(new ExceptionApprovedNotification($exception));

        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار الموافقة على الإجازة...', [
                'to_user_id' => $user->id,
                'exception_id' => $exception->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                '✅ تمت الموافقة على طلب الإجازة',
                "تمت الموافقة على إجازتك بتاريخ {$exception->exception_date}" . ($exception->reason ? "، السبب: {$exception->reason}" : ''),
                ['exception_id' => $exception->id, 'date' => $exception->exception_date]
            );
        }
    }
}
