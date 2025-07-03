<?php
namespace App\Services\Interfaces;
use App\Models\PracticalExamSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PracticalExamServiceInterface
{
        public function scheduleExam(array $data): PracticalExamSchedule;
            public function listAll(int $perPage = 10): LengthAwarePaginator;
    public function getMySchedules(int $perPage = 10): LengthAwarePaginator;

}