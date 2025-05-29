<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChoiceResource extends JsonResource
{
    public function toArray($request)
    {
      //  static $index = 0;
        return [
           'choice_id' => $this->id,
           // 'index' => ++$index,
            'text' => $this->choice_text,
            'is_correct' => (bool) $this->is_correct,
        ];
    }
}
