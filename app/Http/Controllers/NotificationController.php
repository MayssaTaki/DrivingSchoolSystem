<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
}
