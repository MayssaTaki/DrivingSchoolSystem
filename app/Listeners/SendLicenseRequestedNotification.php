<?php
namespace App\Listeners;

use App\Events\LicenseRequested;
use App\Notifications\LicenseRequestedNotification;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLicenseRequestedNotification implements ShouldQueue
{
    public function handle(LicenseRequested $event)
    {
        $student = $event->student;
        $license = $event->license;

        $users = User::whereIn('role', ['admin', 'employee'])->get();

        foreach ($users as $user) {
            logger('📣 إرسال إشعار طلب رخصة جديدة:', [
                'user_id' => $user->id,
                'student_id' => $student->id,
                'license_id' => $license->id,
                'fcm_token_exists' => !empty($user->fcm_token),
            ]);

            $alreadyNotified = $user->notifications()
                ->where('type', \App\Notifications\LicenseRequestedNotification::class)
                ->where('data->student_id', $student->id)
                ->where('data->license_id', $license->id)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار مسبقًا للمستخدم ID: {$user->id} عن الطالب ID: {$student->id} والرخصة ID: {$license->id}");
                continue;
            }

            $user->notify(new LicenseRequestedNotification($student, $license));

           
        }
    }
}
