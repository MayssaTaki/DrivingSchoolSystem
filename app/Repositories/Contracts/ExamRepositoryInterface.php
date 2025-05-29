<?php
namespace App\Repositories\Contracts;
use App\Models\Exam;
use App\Models\ExamAttempt;

interface ExamRepositoryInterface
{
    public function getAllExams();
public function generateStudentExamQuestions(int $studentId, int $limit = 15);
    public function getExamWithQuestions($examId);
    public function submitExam($examId, array $answers);
    public function createExamWithQuestions(array $data): Exam;
public function startExamAttempt(int $examId, int $studentId): ExamAttempt;
public function submitExamAttempt(int $attemptId, array $answers): array;

}
