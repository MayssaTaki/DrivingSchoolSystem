<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\StudentRegistered;
use App\Notifications\StudentRegisteredNotification;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendStudentRegisteredNotification implements ShouldQueue
{
    public function handle(StudentRegistered $event)
    {
        $studentId = $event->student->id;

        logger('=== Listener وصل ===');

        $receivers = User::whereIn('role', ['employee', 'admin'])->get();
        logger('🔥 Listener Triggered داخل SendStudentRegisteredNotification.php');

        foreach ($receivers as $user) {
            $alreadyNotified = $user->notifications()
                ->where('type', \App\Notifications\StudentRegisteredNotification::class)
                ->where('data->student_id', $studentId)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال الإشعار مسبقًا للمستخدم ID: {$user->id} عن المدرب ID: {$studentId}");
                continue;
            }

            logger('📢 إرسال إشعار إلى:', [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'fcm_token_exists' => !empty($user->fcm_token),
            ]);

            $user->notify(new StudentRegisteredNotification($event->student));

           
        }
    }
}
