<?php
namespace App\Listeners;

use App\Events\SessionCompleted;
use App\Notifications\SessionCompletedNotification;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSessionCompletedNotification implements ShouldQueue
{
    public function handle(SessionCompleted $event)
    {
        $booking = $event->booking;

        $users = User::where('role', 'employee')->get();

        foreach ($users as $user) {
            logger('📣 إرسال إشعار إنهاء الجلسة للموظف:', [
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'fcm_token_exists' => !empty($user->fcm_token),
            ]);

            $alreadyNotified = $user->notifications()
                ->where('type', \App\Notifications\SessionCompletedNotification::class)
                ->where('data->booking_id', $booking->id)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار مسبقًا للموظف ID: {$user->id} عن الحجز ID: {$booking->id}");
                continue;
            }

            $user->notify(new SessionCompletedNotification($booking));

            if ($user->fcm_token) {
                logger('🚀 إرسال FCM إشعار إنهاء الجلسة...', [
                    'to_user_id' => $user->id,
                    'booking_id' => $booking->id,
                ]);

                app(FirebaseService::class)->sendNotification(
                    $user->fcm_token,
                    '✅ تم إنهاء جلسة تدريب',
                    "تم إنهاء جلسة تدريب بتاريخ {$booking->session->day_of_week} الساعة {$booking->session->start_time} من قبل المدرب ID: {$booking->trainer_id}.",
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
