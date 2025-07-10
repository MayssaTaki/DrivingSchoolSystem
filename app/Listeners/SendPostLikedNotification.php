<?php
namespace App\Listeners;

use App\Events\PostLiked;
use App\Notifications\PostLikedNotification;
use App\Services\FirebaseService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPostLikedNotification implements ShouldQueue
{
    public function handle(PostLiked $event)
    {
        $post = $event->post;
        $student = $event->student;

        $employees = User::where('role', 'employee')->get();

        foreach ($employees as $user) {
            logger('📣 إرسال إشعار إعجاب بمنشور للموظف:', [
                'user_id' => $user->id,
                'post_id' => $post->id,
                'fcm_token_exists' => !empty($user->fcm_token),
            ]);

            $alreadyNotified = $user->notifications()
                ->where('type', \App\Notifications\PostLikedNotification::class)
                ->where('data->post_id', $post->id)
                ->where('data->student_id', $student->id)
                ->exists();

            if ($alreadyNotified) {
                logger("⛔ تم إرسال إشعار مسبقًا للموظف ID: {$user->id} عن الإعجاب بالمنشور ID: {$post->id}");
                continue;
            }

            $user->notify(new PostLikedNotification($post, $student));

           
        }
    }
}
