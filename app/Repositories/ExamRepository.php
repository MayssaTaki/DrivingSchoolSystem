<?php
namespace App\Repositories;

use App\Models\Exam;
use App\Models\Choice;
use App\Models\Booking;
use DB;
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





    public function hasCompletedSessions(int $trainerId): ?int
    {
        $session = Booking::where('trainer_id', $trainerId)
                          ->where('status', 'completed')
                          ->first();

        return $session?->trainer_id;
    }






   public function getAllExams()
{
    $trainer = auth()->user()->trainer;
    
    if (!$trainer) {
        return collect(); 
    }
    
    return $trainer->exams()->get();
}

   public function getExamWithQuestions($type)
{
    $trainerId = auth()->user()->trainer->id; 

    return Exam::with('questions.choices')
        ->where('trainer_id', $trainerId)
        ->where('type', $type)
        ->firstOrFail(); 
}

public function getAllExamsByTrainerId($trainerId)
    {
        return Exam::where('trainer_id', $trainerId)->get();
    }

    public function getExamWithQuestionsByTrainerAndType($trainerId, $type)
    {
        return Exam::with('questions.choices')
                   ->where('trainer_id', $trainerId)
                   ->where('type', $type)
                   ->firstOrFail();
    }


public function startExamAttemptById(int $examAttemptId): ExamAttempt
{
    $examAttempt = ExamAttempt::findOrFail($examAttemptId);

    if (!$examAttempt->started_at) {
        $examAttempt->started_at = now();
        $examAttempt->save();
    }

    return $examAttempt;
}



public function createExamAttempt(int $examId, int $studentId): ExamAttempt
{
    return ExamAttempt::create([
        'exam_id' => $examId,
        'student_id' => $studentId,
       // 'started_at' => now(),
    ]);
}

public function attachQuestionsToAttempt(int $examAttemptId, array $questionIds): void
{
    $now = now();
    $data = array_map(function ($questionId) use ($examAttemptId, $now) {
        return [
            'exam_attempt_id' => $examAttemptId,
            'question_id' => $questionId,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }, $questionIds);

    DB::table('exam_attempt_questions')->insert($data);
}



}
