<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
            'files' => 'sometimes|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }
}
