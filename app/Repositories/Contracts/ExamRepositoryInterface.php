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
  public function getAllExamsByTrainerId($trainerId);
      public function getExamWithQuestionsByTrainerAndType($trainerId, $type);
    public function createExamWithQuestions(array $data): Exam;
public function startExamAttemptById(int $examAttemptId): ExamAttempt;
//public function submitExamAttempt(int $attemptId, array $answers): array;
public function findById(int $id): ExamAttempt;
public function hasStudentPassedExam(int $studentId, int $examId, int $minScore = 5, ?int $excludeAttemptId = null): bool;

}
