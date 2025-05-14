<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TrainingSchedulesResource extends JsonResource
{
   public function toArray(Request $request): array
    {
        return array_merge(
            $this->commonAttributes(),
            $this->shouldShowFullDetails() ? $this->adminOrEmployeeDetails() : $this->studentLimitedDetails()
        );
    }
 protected function commonAttributes(): array
    {
       $user = auth()->user();
        if (!in_array($user?->role, ['admin', 'employee']) && $this->status !== 'active') {
        return [];
    }

    return [
        'day_key' => $this->day_of_week,
        'time_range' => substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5),
    ];
    }
    
    protected function getTranslatedDay($day)
    {
        $days = [
            'saturday' => 'السبت',
            'sunday' => 'الأحد',
            'monday' => 'الإثنين',
            'tuesday' => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس',
        ];
        
        return $days[$day] ?? $day;
    }
 protected function adminOrEmployeeDetails(): array
    {
        return [
              'start_time' => substr($this->start_time, 0, 5),
            'end_time' => substr($this->end_time, 0, 5),
           'is_recurring' => (bool)$this->is_recurring,
            'valid_from' => Carbon::parse($this->valid_from)->format('Y-m-d'),
            'valid_to' => $this->valid_to ? Carbon::parse($this->valid_to)->format('Y-m-d') : null,
            'status' => $this->status,
          
        ];
    }
     protected function studentLimitedDetails(): array
    {
        return []; 
    }

    protected function shouldShowFullDetails(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'employee']);
    }
}