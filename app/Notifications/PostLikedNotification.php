<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PostLikedNotification extends Notification
{
    public function __construct(public $post, public $student) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '❤️ إعجاب جديد بمنشور',
            'body'  => "{$this->student->user->name} أُعجب بمنشور ",
            'post_id' => $this->post->id,
            'student_id' => $this->student->id,
        ];
    }
}
