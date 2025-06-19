<?php

namespace App\Services\Interfaces;

interface ExamServiceInterface
{
    public function createExamWithQuestions(array $data);
    public function listExams();
    public function showExam($type);
    public function listExamsByTrainer($trainerId);
    public function showExamByTrainerAndType($trainerId, $type);
    public function processSubmission($examId, $answers);
    public function startExamByAttemptId(int $examAttemptId);
public function getExamQuestionsForStudent(string $type, int $count = 10, int $studentId);    public function getRandomQuestionsForTrainer(int $trainerId, string $type, int $count = 10);
    public function extractQuestionText($text);
    public function extractQuestionNumber($text);
    public function submitExam(int $attemptId, array $answers);
    public function evaluateStudent(int $studentId, float $passPercentage = 60.0): array;
}
