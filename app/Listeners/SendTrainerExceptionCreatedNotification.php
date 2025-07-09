<?php
namespace App\Listeners;

use App\Events\TrainerExceptionCreated;
use App\Notifications\TrainerExceptionCreatedNotification;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTrainerExceptionCreatedNotification implements ShouldQueue
{
    public function handle(TrainerExceptionCreated $event)
    {
        $trainer = $event->trainer;
        $count = $event->count;
        $reason = $event->reason;

        $users = User::whereIn('role', ['admin', 'employee'])->get();

        foreach ($users as $user) {
            logger('📣 إرسال إشعار تسجيل إجازة جديدة:', [
                'user_id' => $user->id,
                'trainer_id' => $trainer->id,
                'count' => $count,
                'fcm_token_exists' => !empty($user->fcm_token),
            ]);

            $alreadyNotified = $user->notifications()
                ->where('type', \App\Notifications\TrainerExceptionCreatedNotification::class)
                ->where('data->trainer_id', $trainer->id)
                ->where('data->count', $count)
                ->where('data->reason', $reason)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار مسبقًا للمستخدم ID: {$user->id} عن trainer ID: {$trainer->id}");
                continue;
            }

            $user->notify(new TrainerExceptionCreatedNotification($trainer, $count, $reason));

            if ($user->fcm_token) {
                logger('🚀 إرسال FCM إشعار تسجيل إجازة...', [
                    'to_user_id' => $user->id,
                    'trainer_id' => $trainer->id,
                ]);

                app(FirebaseService::class)->sendNotification(
                    $user->fcm_token,
                    '📆 طلب إجازة جديد',
                    "{$trainer->first_name} {$trainer->last_name} طلب إجازة لعدد {$count} يوم" . ($reason ? "، السبب: {$reason}" : ''),
                    ['trainer_id' => $trainer->id, 'count' => $count, 'reason' => $reason]
                );
            }
        }
    }
}
