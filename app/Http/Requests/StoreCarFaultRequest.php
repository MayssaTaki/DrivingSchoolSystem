<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class StoreCarFaultRequest extends FormRequest
{
    public function rules()
    {
        return [
            'car_id' => 'required|exists:cars,id',
            'comment' => 'required|string|max:1000',
            'booking_id' => 'nullable|exists:bookings,id',
        ];
    }
}
