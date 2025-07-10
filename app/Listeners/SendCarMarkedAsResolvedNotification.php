<?php
namespace App\Listeners;

use App\Events\CarMarkedAsResolved;
use App\Notifications\CarMarkedAsResolvedNotification;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCarMarkedAsResolvedNotification implements ShouldQueue
{
    public function handle(CarMarkedAsResolved $event)
    {
        $fault = $event->fault;
        $car = $event->car;

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            logger('📣 إرسال إشعار انتهاء التصليح:', [
                'admin_id' => $admin->id,
                'car_id' => $car->id,
                'fault_id' => $fault->id,
                'fcm_token_exists' => !empty($admin->fcm_token),
            ]);

            $alreadyNotified = $admin->notifications()
                ->where('type', \App\Notifications\CarMarkedAsResolvedNotification::class)
                ->where('data->fault_id', $fault->id)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار مسبقًا للادمن ID: {$admin->id} عن العطل ID: {$fault->id}");
                continue;
            }

            $admin->notify(new CarMarkedAsResolvedNotification($fault, $car));

           
        }
    }
}
