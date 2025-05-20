<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetTrainerExceptionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'trainer_id' => 'required|integer|exists:trainers,id',
        ];
    }
}
