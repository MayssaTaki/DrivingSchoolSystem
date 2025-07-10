<?php
namespace App\Listeners;

use App\Events\SessionAutoBooked;
use App\Notifications\SessionAutoBookedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSessionAutoBookedNotification implements ShouldQueue
{
    public function handle(SessionAutoBooked $event)
    {
        $booking = $event->booking;
        $trainer = $booking->session?->trainer; 
        $user = $trainer?->user; 

        if (!$user) {
            logger("⛔ لم يتم العثور على المدرب أو المستخدم للجلسة ID: {$booking->session_id}");
            return;
        }

        logger('📣 إرسال إشعار حجز تلقائي للمدرب:', [
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

       
        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\SessionAutoBookedNotification::class)
            ->where('data->booking_id', $booking->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار مسبقًا للمدرب ID: {$user->id} عن الحجز ID: {$booking->id}");
            return;
        }

     
        $user->notify(new SessionAutoBookedNotification($booking));

       
    }
}
