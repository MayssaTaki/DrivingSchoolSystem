<?php
namespace App\Listeners;

use App\Events\SessionCancelled;
use App\Notifications\SessionCancelledNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSessionCancelledNotification implements ShouldQueue
{
    public function handle(SessionCancelled $event)
    {
        $booking = $event->booking;
        $session = $event->session;
        $cancelledByStudent = $event->cancelledByStudent;

        $recipientUser = $cancelledByStudent
            ? $session->trainer?->user
            : $booking->student?->user;

        if (!$recipientUser) {
            logger("⛔ لم يتم العثور على المستخدم المستلم للجلسة ID: {$session->id}");
            return;
        }

        logger('📣 إرسال إشعار إلغاء الجلسة للطرف الآخر:', [
            'user_id' => $recipientUser->id,
            'booking_id' => $booking->id,
            'cancelled_by_student' => $cancelledByStudent,
            'fcm_token_exists' => !empty($recipientUser->fcm_token),
        ]);

        $alreadyNotified = $recipientUser->notifications()
            ->where('type', \App\Notifications\SessionCancelledNotification::class)
            ->where('data->booking_id', $booking->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار الإلغاء مسبقًا للمستخدم ID: {$recipientUser->id} عن الحجز ID: {$booking->id}");
            return;
        }

        $recipientUser->notify(new SessionCancelledNotification($booking, $session, $cancelledByStudent));

        if ($recipientUser->fcm_token) {
            $who = $cancelledByStudent ? 'الطالب' : 'المدرب';
            logger('🚀 إرسال FCM إشعار إلغاء الجلسة...', [
                'to_user_id' => $recipientUser->id,
                'booking_id' => $booking->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $recipientUser->fcm_token,
                '⚠️ تم إلغاء جلسة تدريب',
                "قام {$who} بإلغاء جلسة التدريب بتاريخ {$session->session_date} الساعة {$session->start_time}.",
                [
                    'booking_id' => $booking->id,
                    'session_id' => $session->id,
                    'cancelled_by_student' => $cancelledByStudent
                ]
            );
        }
    }
}
