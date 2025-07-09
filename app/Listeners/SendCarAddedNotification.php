<?php
namespace App\Listeners;

use App\Events\CarAdded;
use App\Notifications\CarAddedNotification;
use App\Services\FirebaseService;
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
                'fcm_token_exists' => !empty($admin->fcm_token),
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

            if ($admin->fcm_token) {
                logger('🚀 إرسال FCM إشعار إضافة سيارة...', [
                    'to_admin_id' => $admin->id,
                    'car_id' => $car->id
                ]);

                app(FirebaseService::class)->sendNotification(
                    $admin->fcm_token,
                    '🚗 تمت إضافة سيارة جديدة',
                    "{$car->make} {$car->model} تمت إضافتها للنظام.",
                    ['car_id' => $car->id]
                );
            }
        }
    }
}
