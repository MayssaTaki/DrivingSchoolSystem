<?php
namespace App\Listeners;

use App\Events\SessionStarted;
use App\Notifications\SessionStartedNotification;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSessionStartedNotification implements ShouldQueue
{
    public function handle(SessionStarted $event)
    {
        $booking = $event->booking;

        $users = User::where('role', 'employee')->get();

        foreach ($users as $user) {
            logger('📣 إرسال إشعار بدء جلسة تدريب للموظف:', [
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'fcm_token_exists' => !empty($user->fcm_token),
            ]);

            
            $alreadyNotified = $user->notifications()
                ->where('type', \App\Notifications\SessionStartedNotification::class)
                ->where('data->booking_id', $booking->id)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار مسبقًا للموظف ID: {$user->id} عن الحجز ID: {$booking->id}");
                continue;
            }

           
            $user->notify(new SessionStartedNotification($booking));

          
            if ($user->fcm_token) {
                logger('🚀 إرسال FCM إشعار بدء الجلسة...', [
                    'to_user_id' => $user->id,
                    'booking_id' => $booking->id,
                ]);

                app(FirebaseService::class)->sendNotification(
                    $user->fcm_token,
                    '🚀 تم بدء جلسة تدريب',
                    "تم بدء جلسة تدريب بتاريخ {$booking->session->day_of_week} الساعة {$booking->session->start_time} بواسطة المدرب ID: {$booking->trainer_id}.",
                    [
                        'booking_id' => $booking->id,
                        'session_id' => $booking->session_id,
                        'trainer_id' => $booking->trainer_id
                    ]
                );
            }
        }
    }
}
