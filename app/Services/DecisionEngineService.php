<?php

namespace App\Services;

use App\Repositories\StudentRepository;
use App\Repositories\TrainingSessionRepository;
use App\Repositories\CarRepository;
use Illuminate\Support\Carbon;

class DecisionEngineService
{
    protected $studentRepo;
    protected $sessionRepo;
    protected $carRepo;

    public function __construct(
        StudentRepository $studentRepo,
        TrainingSessionRepository $sessionRepo,
        CarRepository $carRepo
    ) {
        $this->studentRepo = $studentRepo;
        $this->sessionRepo = $sessionRepo;
        $this->carRepo = $carRepo;
    }

    /**
     * يختار أفضل جلسة وسيارة بناءً على يوم ووقت الطالب
     */
    public function chooseBestSessionAndCar(int $studentId, string $preferredDate, string $preferredTime): array
    {
        $student = $this->studentRepo->find($studentId);

        $availableSessions = $this->getAvailableSessionsForStudent($preferredDate, $preferredTime);
        $availableCars = $this->carRepo->getAvailableCars();

        if ($availableSessions->isEmpty() || empty($availableCars)) {
            throw new \Exception("لا توجد جلسات أو سيارات متاحة حالياً.");
        }

        // الأقرب من حيث الوقت والتاريخ
        $bestSession = $availableSessions->sortBy(function ($session) {
            return Carbon::parse($session->start_time);
        })->first();

        $bestCar = collect($availableCars)->firstWhere('trainer_id', $bestSession->trainer_id)
                  ?? collect($availableCars)->first();

        return [
            'session_id' => $bestSession->id,
            'car_id' => $bestCar->id,
        ];
    }

    /**
     * جلب الجلسات المتاحة بناء على يوم ووقت الطالب
     */
public function getAvailableSessionsForStudent(string $preferredDate, string $preferredTime)
{
    $preferredDateTime = Carbon::parse("{$preferredDate} {$preferredTime}");

    return collect($this->sessionRepo->getAvailableSessions())
        ->filter(function ($session) use ($preferredDateTime) {
            $sessionDateTime = Carbon::parse("{$session->session_date} {$session->start_time}");
            return $sessionDateTime->greaterThanOrEqualTo($preferredDateTime); // لا نأخذ الجلسات القديمة
        })
        ->sortBy(function ($session) use ($preferredDateTime) {
            $sessionDateTime = Carbon::parse("{$session->session_date} {$session->start_time}");
            return $sessionDateTime->diffInMinutes($preferredDateTime); // أقرب فرق زمني
        })
        ->take(10);
}






}
