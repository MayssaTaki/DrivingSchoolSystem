<?php
namespace App\Listeners;

use App\Events\CarAdded;
use App\Notifications\CarAddedNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCarAddedNotification implements ShouldQueue
{
    public function handle(CarAdded $event)
    {
        $car = $event->car;

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            logger('📣 إرسال إشعار إضافة سيارة للادمن:', [
                'admin_id' => $admin->id,
                'car_id' => $car->id,
            ]);

            $alreadyNotified = $admin->notifications()
                ->where('type', \App\Notifications\CarAddedNotification::class)
                ->where('data->car_id', $car->id)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار إضافة السيارة مسبقًا للادمن ID: {$admin->id} عن السيارة ID: {$car->id}");
                continue;
            }

            $admin->notify(new CarAddedNotification($car));
        }
    }
}
