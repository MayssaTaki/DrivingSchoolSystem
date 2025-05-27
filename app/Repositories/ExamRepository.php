<?php
namespace App\Repositories;

use App\Models\Exam;
use App\Models\ExamAttempt;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Repositories\Contracts\ExamRepositoryInterface;

class ExamRepository implements ExamRepositoryInterface
{



public function createExamWithQuestions(array $data): Exam
{
    $exam = Exam::create([
        'title' => $data['title'],
        'duration_minutes' => $data['duration_minutes'],
        'trainer_id' => $data['trainer_id'],
    ]);

    foreach ($data['questions'] as $q) {
        $imagePath = null;

        if (isset($q['image'])) {
            $image = $q['image'];
            $imagePath = $image->store('questions', 'public');
        }

        $question = $exam->questions()->create([
            'question_text' => $q['question_text'],
            'image_path' => $imagePath,
        ]);

        foreach ($q['choices'] as $choice) {
            $question->choices()->create([
                'choice_text' => $choice['text'],
                'is_correct' => $choice['is_correct'],
            ]);
        }
    }

    return $exam->load('questions.choices');
}





    public function getAllExams()
    {
        return Exam::all();
    }

    public function getExamWithQuestions($examId)
    {
        return Exam::with('questions.choices')->findOrFail($examId);
    }

    public function submitExam($examId, array $answers)
    {
        $exam = Exam::with('questions.choices')->findOrFail($examId);
        $score = 0;

        foreach ($exam->questions as $question) {
            $userAnswer = $answers[$question->id] ?? null;
            $correctChoice = $question->choices->where('is_correct', true)->first();
            if ($correctChoice && $correctChoice->id == $userAnswer) {
                $score++;
            }
        }

        return ['score' => $score, 'total' => $exam->questions->count()];
    }

    public function startExamAttempt(int $examId, int $studentId): ExamAttempt
{
    return ExamAttempt::create([
        'exam_id' => $examId,
        'student_id' => $studentId,
        'started_at' => now(),
    ]);
}
public function submitExamAttempt(int $attemptId, array $answers): array
{
    $attempt = ExamAttempt::with('exam.questions.choices')->findOrFail($attemptId);

    $score = 0;
    $total = $attempt->exam->questions->count();

    foreach ($attempt->exam->questions as $question) {
        $userAnswerId = $answers[$question->id] ?? null;
        $correct = $question->choices->firstWhere('is_correct', true);

        if ($correct && $correct->id == $userAnswerId) {
            $score++;
        }
    }

    $attempt->update([
        'score' => $score,
        'submitted_at' => now()
    ]);

    return ['score' => $score, 'total' => $total];
}


}
