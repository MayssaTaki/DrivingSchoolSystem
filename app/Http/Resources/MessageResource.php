<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\Interfaces\ImageServiceInterface;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        $content = $this->content;

        if ($this->type === 'image' || $this->type === 'file') {
            $content = app(ImageServiceInterface::class)->getSignedUrl($this->content);
        }

        return [
            
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            
            'content' => $content,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'id' => $this->id,
        ];
    }
}

