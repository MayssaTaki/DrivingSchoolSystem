<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'message' => $this->message,
            'context' => $this->context,

            'channel' => $this->channel,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
