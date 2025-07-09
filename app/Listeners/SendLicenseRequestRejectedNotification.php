<?php
namespace App\Listeners;

use App\Events\LicenseRequestRejected;
use App\Notifications\LicenseRequestRejectedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLicenseRequestRejectedNotification implements ShouldQueue
{
    public function handle(LicenseRequestRejected $event)
    {
        $licenseRequest = $event->licenseRequest;
        $reason = $event->reason;

        $student = $licenseRequest->student; 
        if (!$student || !$student->user) {
            logger("⛔ لم يتم العثور على الطالب أو المستخدم المرتبط بالطلب ID: {$licenseRequest->id}");
            return;
        }

        $user = $student->user;

        logger('📣 إرسال إشعار رفض طلب الرخصة:', [
            'user_id' => $user->id,
            'license_request_id' => $licenseRequest->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\LicenseRequestRejectedNotification::class)
            ->where('data->license_request_id', $licenseRequest->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار الرفض مسبقًا للطالب ID: {$user->id} عن الطلب ID: {$licenseRequest->id}");
            return;
        }

        $user->notify(new LicenseRequestRejectedNotification($licenseRequest, $reason));

        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار رفض طلب الرخصة...', [
                'to_user_id' => $user->id,
                'license_request_id' => $licenseRequest->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                '❌ تم رفض طلب الرخصة',
                "تم رفض طلبك للرخصة بالكود: {$licenseRequest->license->code}. السبب: {$reason}",
                [
                    'license_request_id' => $licenseRequest->id,
                    'license_id' => $licenseRequest->license_id,
                    'license_code' => $licenseRequest->license->code,
                    'reason' => $reason
                ]
            );
        }
    }
}
