<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'public_key' => 'required|string',
            'secret'     => 'required|digits:8',
            'serial'     => 'required|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
