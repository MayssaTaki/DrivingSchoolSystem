<?php
namespace App\Listeners;

use App\Events\PostCreated;
use App\Notifications\PostCreatedNotification;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPostCreatedNotification implements ShouldQueue
{
    public function handle(PostCreated $event)
    {
        $post = $event->post;

        $students = User::where('role', 'student')->get();

        foreach ($students as $user) {
            logger('📣 إرسال إشعار منشور جديد للطالب:', [
                'user_id' => $user->id,
                'post_id' => $post->id,
                'fcm_token_exists' => !empty($user->fcm_token),
            ]);

            $alreadyNotified = $user->notifications()
                ->where('type', \App\Notifications\PostCreatedNotification::class)
                ->where('data->post_id', $post->id)
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $user->notify(new PostCreatedNotification($post));

            if ($user->fcm_token) {
                app(FirebaseService::class)->sendNotification(
                    $user->fcm_token,
                    '📢 منشور جديد',
                    "تم نشر منشور جديد بعنوان: {$post->title}",
                    [
                        'post_id' =>(string) $post->id,
                        'creator' => $post->user?->name ?? 'موظف'
                    ]
                );
            }
        }
    }
}
