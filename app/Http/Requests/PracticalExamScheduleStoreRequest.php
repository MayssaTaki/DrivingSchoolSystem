<?php
namespace App\Http\Requests;
use Illuminate\Support\Facades\Gate;
use App\Models\PracticalExamSchedule;

use Illuminate\Foundation\Http\FormRequest;
class PracticalExamScheduleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', PracticalExamSchedule::class);
    }

    public function rules(): array
    {
        return [
            'license_request_id' => 'required|exists:license_requests,id',
            'exam_date' => 'required|date|after_or_equal:today',
            'exam_time' => 'required|date_format:H:i',
        ];
    }
}
