<?php
namespace App\Listeners;

use App\Events\LicenseCreated;
use App\Notifications\LicenseCreatedNotification;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLicenseCreatedNotification implements ShouldQueue
{
    public function handle(LicenseCreated $event)
    {
        $license = $event->license;

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            logger('📣 إرسال إشعار إضافة رخصة جديدة:', [
                'admin_id' => $admin->id,
                'license_id' => $license->id,
                'code' => $license->code,
                'fcm_token_exists' => !empty($admin->fcm_token),
            ]);

            $alreadyNotified = $admin->notifications()
                ->where('type', \App\Notifications\LicenseCreatedNotification::class)
                ->where('data->license_id', $license->id)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار مسبقًا للادمن ID: {$admin->id} عن الرخصة ID: {$license->id}");
                continue;
            }

            $admin->notify(new LicenseCreatedNotification($license));

            
        }
    }
}
