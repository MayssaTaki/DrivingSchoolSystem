<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class StoreTrainerReviewRequest extends FormRequest
{
    public function rules()
    {
        return [
            'trainer_id' => 'required|exists:trainers,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }
}
