<?php
namespace App\Listeners;

use App\Events\CarFaultSubmitted;
use App\Notifications\CarFaultSubmittedNotification;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCarFaultSubmittedNotification implements ShouldQueue
{
    public function handle(CarFaultSubmitted $event)
    {
        $fault = $event->fault;

        $users = User::whereIn('role', ['admin', 'employee'])->get();

        foreach ($users as $user) {
            logger('📣 إرسال إشعار تسجيل عطل جديد:', [
                'user_id' => $user->id,
                'fault_id' => $fault->id,
                'car_id' => $fault->car_id,
                'fcm_token_exists' => !empty($user->fcm_token),
            ]);

            $alreadyNotified = $user->notifications()
                ->where('type', \App\Notifications\CarFaultSubmittedNotification::class)
                ->where('data->fault_id', $fault->id)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار مسبقًا للمستخدم ID: {$user->id} عن العطل ID: {$fault->id}");
                continue;
            }

            $user->notify(new CarFaultSubmittedNotification($fault));

            if ($user->fcm_token) {
                logger('🚀 إرسال FCM إشعار تسجيل عطل جديد...', [
                    'to_user_id' => $user->id,
                    'fault_id' => $fault->id
                ]);

                app(FirebaseService::class)->sendNotification(
                    $user->fcm_token,
                    '🚨 تم تسجيل عطل جديد',
                    "تم تسجيل عطل للسيارة ID: {$fault->car_id}",
                    ['fault_id' => $fault->id, 'car_id' => $fault->car_id]
                );
            }
        }
    }
}
