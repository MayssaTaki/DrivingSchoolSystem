<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TrainerReviewedNotification extends Notification
{
    public function __construct(public $review) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => '⭐ تقييم جديد للمدرب',
            'body'  => "تم إضافة تقييم جديد للمدرب  {$this->review->trainer->first_name}  {$this->review->trainer->last_name}    بتقييم: {$this->review->rating}",
            'review_id' => $this->review->id,
            'trainer_id' => $this->review->trainer_id,
            'rating' => $this->review->rating,
        ];
    }
}
