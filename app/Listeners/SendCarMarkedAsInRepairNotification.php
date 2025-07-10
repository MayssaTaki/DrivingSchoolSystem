<?php
namespace App\Listeners;

use App\Events\CarMarkedAsInRepair;
use App\Notifications\CarMarkedAsInRepairNotification;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCarMarkedAsInRepairNotification implements ShouldQueue
{
    public function handle(CarMarkedAsInRepair $event)
    {
        $fault = $event->fault;
        $car = $event->car;

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            logger('📣 إرسال إشعار تحويل السيارة للتصليح:', [
                'admin_id' => $admin->id,
                'car_id' => $car->id,
                'fault_id' => $fault->id,
                'fcm_token_exists' => !empty($admin->fcm_token),
            ]);

            $alreadyNotified = $admin->notifications()
                ->where('type', \App\Notifications\CarMarkedAsInRepairNotification::class)
                ->where('data->fault_id', $fault->id)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار مسبقًا للادمن ID: {$admin->id} عن العطل ID: {$fault->id}");
                continue;
            }

            $admin->notify(new CarMarkedAsInRepairNotification($fault, $car));

            
        }
    }
}
