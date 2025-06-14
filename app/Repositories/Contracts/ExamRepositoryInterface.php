<?php
namespace App\Repositories\Contracts;
use App\Models\Exam;
use App\Models\ExamAttempt;

interface ExamRepositoryInterface
{
    public function getAllExams();
    public function hasCompletedSessions(int $trainerId): ?int; 
    public function getExamWithQuestions($type);
 // public function getRandomQuestionsForTrainer(int $trainerId, string $type, int $count = 10);
  //  public function submitExam(array $answers);
  
    public function createExamWithQuestions(array $data): Exam;
public function startExamAttemptById(int $examAttemptId): ExamAttempt;
//public function submitExamAttempt(int $attemptId, array $answers): array;

}
