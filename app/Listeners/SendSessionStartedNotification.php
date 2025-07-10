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

          
           
        }
    }
}
