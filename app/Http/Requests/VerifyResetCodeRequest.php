<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyResetCodeRequest extends FormRequest
{
    public function rules()
    {
        return [
'email' => ['required', 'email', 'exists:users,email'],
            'code' => 'required|string|size:6',
        ];
    }
}
