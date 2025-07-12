<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Queue\SerializesModels;

class SendMessage {
    use SerializesModels;

    public $message;

    public function __construct(Message $message) {
        $this->message = $message;
    }
}
