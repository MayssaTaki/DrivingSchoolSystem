<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PostCreatedNotification extends Notification
{
    public function __construct(public $post) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '📢 منشور جديد',
            'body'  => "تم نشر منشور جديد بعنوان: {$this->post->title}",
            'post_id' => (string) $this->post->id,
            'creator' => $this->post->user?->name ?? 'موظف',
        ];
    }
}
