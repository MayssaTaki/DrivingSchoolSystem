<?php
namespace App\Listeners;

use App\Events\TrainingSchedulesCreated;
use App\Notifications\TrainingSchedulesCreatedNotification;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTrainingSchedulesCreatedNotification implements ShouldQueue
{
    public function handle(TrainingSchedulesCreated $event)
    {
        $trainer = $event->trainer;
        $count = $event->count;

        $users = User::whereIn('role', ['admin', 'employee'])->get();

        foreach ($users as $user) {
            logger('📣 إرسال إشعار إضافة جداول تدريب:', [
                'user_id' => $user->id,
                'trainer_id' => $trainer->id,
                'count' => $count,
                'fcm_token_exists' => !empty($user->fcm_token),
            ]);

            $alreadyNotified = $user->notifications()
                ->where('type', \App\Notifications\TrainingSchedulesCreatedNotification::class)
                ->where('data->trainer_id', $trainer->id)
                ->where('data->count', $count)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار مسبقًا للمستخدم ID: {$user->id} عن trainer ID: {$trainer->id} وعدد الجداول: {$count}");
                continue;
            }

            $user->notify(new TrainingSchedulesCreatedNotification($trainer, $count));

           
        }
    }
}
