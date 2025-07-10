<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
class NotificationController extends Controller
{
    public function index(Request $request)
    {  logger('🔎 المستخدم الحالي:', [
        'id' => $request->user()->id,
        'name' => $request->user()->name
    ]);
        return $request->user()->notifications()->latest()->get()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? '',
                'body' => $notification->data['body'] ?? '',
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        });
    }

    public function markAsRead($id, Request $request)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['message' => 'Marked as read']);
    }


    public function sendFakeNotification()
    {
        $deviceToken = 'fgooU4_YSMeF2xVyAJxJaj:APA91bHOp5P9qhMI4AQ5h1C3RTF_F8YA-KOV0_q_ZzS0061Gb3NQO34wO5WpwyqR6sw4-o-GEUKWl3imeeRKD8xWfx-aRNDBOYe1bIrgU_413zmXvTv7bIM';

        try {
            $result = app(FirebaseService::class)->sendNotification(
                $deviceToken,
                'كارما',
                'حبيت زكرك فيا',
                ['test' => 'true'] 
            );

            return response()->json([
                'message' => 'تم إرسال الإشعار بنجاح!',
                'fcm_response' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'فشل في إرسال الإشعار',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
