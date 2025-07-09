<?php
namespace App\Listeners;

use App\Events\LicenseRequestApproved;
use App\Notifications\LicenseRequestApprovedNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLicenseRequestApprovedNotification implements ShouldQueue
{
    public function handle(LicenseRequestApproved $event)
    {
        $licenseRequest = $event->licenseRequest;

        $student = $licenseRequest->student; 
        if (!$student || !$student->user) {
            logger("⛔ لم يتم العثور على الطالب أو المستخدم المرتبط بالطلب ID: {$licenseRequest->id}");
            return;
        }

        $user = $student->user;

        logger('📣 إرسال إشعار الموافقة على طلب الرخصة:', [
            'user_id' => $user->id,
            'license_request_id' => $licenseRequest->id,
            'fcm_token_exists' => !empty($user->fcm_token),
        ]);

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\LicenseRequestApprovedNotification::class)
            ->where('data->license_request_id', $licenseRequest->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار مسبقًا للطالب ID: {$user->id} عن الطلب ID: {$licenseRequest->id}");
            return;
        }

        $user->notify(new LicenseRequestApprovedNotification($licenseRequest));

        if ($user->fcm_token) {
            logger('🚀 إرسال FCM إشعار الموافقة...', [
                'to_user_id' => $user->id,
                'license_request_id' => $licenseRequest->id,
            ]);

            app(FirebaseService::class)->sendNotification(
                $user->fcm_token,
                '✅ تمت الموافقة على طلب الرخصة',
                "تمت الموافقة على طلبك للرخصة بالكود: {$licenseRequest->license->code}",
                [
                    'license_request_id' => $licenseRequest->id,
                    'license_id' => $licenseRequest->license_id,
                    'license_code' => $licenseRequest->license->code,
                ]
            );
        }
    }
}
