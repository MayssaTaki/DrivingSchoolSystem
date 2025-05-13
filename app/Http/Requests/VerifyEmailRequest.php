<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class VerifyEmailRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|string|size:6',
        'user_id' => 'required|exists:users,id',
        ];
    }
}
