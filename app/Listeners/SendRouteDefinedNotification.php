<?php
namespace App\Listeners;

use App\Events\RouteDefined;
use App\Notifications\RouteDefinedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendRouteDefinedNotification implements ShouldQueue
{
    public function handle(RouteDefined $event)
    {
        $route = $event->route;
        $booking = $route->booking;
        $student = $booking->student;

        if (!$student || !$student->user) {
            logger("⛔ لم يتم العثور على الطالب أو المستخدم للحجز ID: {$booking->id}");
            return;
        }

        $user = $student->user;

        $alreadyNotified = $user->notifications()
            ->where('type', \App\Notifications\RouteDefinedNotification::class)
            ->where('data->route_id', $route->id)
            ->exists();

        if ($alreadyNotified) {
            logger("⛔ تم إرسال إشعار تحديد المسار مسبقًا للطالب ID: {$user->id} عن المسار ID: {$route->id}");
            return;
        }

        $user->notify(new RouteDefinedNotification($route));
        logger('📣 تم إرسال إشعار تحديد المسار للطالب:', [
            'user_id' => $user->id,
            'route_id' => $route->id,
        ]);
    }
}
