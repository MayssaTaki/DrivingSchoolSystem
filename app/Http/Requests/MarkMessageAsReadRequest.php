<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkMessageAsReadRequest extends FormRequest {
    public function rules() {
        return [
            'message_id' => 'required|exists:messages,id',
        ];
    }
}
