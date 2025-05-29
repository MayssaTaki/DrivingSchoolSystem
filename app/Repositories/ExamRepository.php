<?php
namespace App\Repositories;

use App\Models\Exam;
use App\Models\Choice;

use App\Models\ExamAttempt;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Repositories\Contracts\ExamRepositoryInterface;

class ExamRepository implements ExamRepositoryInterface
{



public function createExamWithQuestions(array $data): Exam
{
    $exam = Exam::create([
        'type' => $data['type'], 
        'duration_minutes' => $data['duration_minutes'],
        'trainer_id' => $data['trainer_id'],
    ]);

    $questionsData = [];
    $choicesData = [];

    foreach ($data['questions'] as $qIndex => $q) {
        $imagePath = null;

        if (isset($q['image'])) {
            $image = $q['image'];
            $imagePath = $image->store('questions', 'public');
        }

        $questionsData[] = [
            'exam_id' => $exam->id,
            'question_text' => $q['question_text'],
            'image_path' => $imagePath,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    $exam->questions()->insert($questionsData);

    $questions = $exam->questions()->get();

    foreach ($questions as $index => $question) {
        $originalChoices = $data['questions'][$index]['choices'] ?? [];

        foreach ($originalChoices as $choice) {
            $choicesData[] = [
                'question_id' => $question->id,
                'choice_text' => $choice['text'],
                'is_correct' => $choice['is_correct'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    Choice::insert($choicesData);

    return $exam->load('questions.choices');
}

public function generateStudentExamQuestions(int $studentId, int $limit = 15)
{
    // ✅ استخراج المدرّب من آخر حجز للطالب
    $latestBooking = \App\Models\Booking::where('student_id', $studentId)
        ->latest()
        ->first();

    if (!$latestBooking || !$latestBooking->trainer_id) {
        throw new \Exception('لم يتم العثور على أي حجز سابق للطالب.');
    }

    $trainerId = $latestBooking->trainer_id;

    // ✅ استخراج الأسئلة من جميع الامتحانات الخاصة بالمدرب
    $questions = \App\Models\Question::with('choices')
        ->whereHas('exam', function ($query) use ($trainerId) {
            $query->where('trainer_id', $trainerId);
        })
        ->inRandomOrder()
        ->limit($limit)
        ->get();

    if ($questions->isEmpty()) {
        throw new \Exception('لا توجد أسئلة متاحة لهذا المدرب.');
    }

    return $questions;
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
