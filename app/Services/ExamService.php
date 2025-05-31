<?php
namespace App\Services;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Exam;
use App\Models\Question;

use App\Models\ExamAttemptQuestion;

use App\Models\ExamAttempt;

use App\Repositories\Contracts\ExamRepositoryInterface;

class ExamService
{
    protected $examRepo;
   protected ActivityLoggerService $activityLogger;
    protected LogService $logService;
    public function __construct(ExamRepositoryInterface $examRepo,
    ActivityLoggerService $activityLogger,
        LogService $logService,        TransactionService $transactionService,
)
    {
        $this->examRepo = $examRepo;
          $this->activityLogger = $activityLogger;
        $this->logService = $logService;
                $this->transactionService = $transactionService;

    }




public function createExamWithQuestions(array $data)
{
    try {
        return $this->transactionService->run(function () use ($data) {
            $exam = $this->examRepo->createExamWithQuestions($data);

            $this->activityLogger->log(
                'تم إنشاء امتحان جديد',
                [
                    'title' => $exam->title,
                    'duration' => $exam->duration_minutes,
                    'trainer_id' => $exam->trainer_id,
                ],
                'exams',
                $exam,
                auth()->user(),
                'created'
            );

            return $exam;
        });
    } catch (\Exception $e) {
        $this->logService->log(
            'error',
            'فشل إنشاء الامتحان',
            [
                'message' => $e->getMessage(),
                'trainer_id' => $data['trainer_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ],
            'exams'
        );

        throw $e;
    }
}

    public function listExams()
    {
        return $this->examRepo->getAllExams();
    }

    public function showExam($id)
    {
        return $this->examRepo->getExamWithQuestions($id);
    }

    public function processSubmission($examId, $answers)
    {
        return $this->examRepo->submitExam($examId, $answers);
    }





public function startExamByAttemptId(int $examAttemptId)
{
    try {
        return $this->transactionService->run(function () use ($examAttemptId) {
            $examAttempt = $this->examRepo->startExamAttemptById($examAttemptId);

            $this->activityLogger->log(
                'بدء محاولة امتحان',
                [
                    'exam_id' => $examAttempt->exam_id,
                    'student_id' => $examAttempt->student_id,
                    'started_at' => $examAttempt->started_at,
                ],
                'exam_attempts',
                $examAttempt,
                auth()->user(),
                'start'
            );

            return $examAttempt;
        });
    } catch (\Exception $e) {
        $this->logService->log(
            'error',
            'فشل بدء محاولة الامتحان',
            [
                'message' => $e->getMessage(),
                'exam_attempt_id' => $examAttemptId,
                'trace' => $e->getTraceAsString(),
            ],
            'exam_attempts'
        );

        throw $e;
    }
}












public function getExamQuestionsForStudent(int $trainerId, string $type, int $count = 10, int $studentId)
{
    $trainerId = $this->examRepo->hasCompletedSessions($trainerId);
    if (!$trainerId) {
        return null;
    }

    $examData = $this->getRandomQuestionsForTrainer($trainerId, $type, $count);

    $examAttempt = $this->examRepo->createExamAttempt($examData['exam_info']['exam_id'], $studentId);

    $questionIds = collect($examData['questions'])->pluck('question_id')->toArray();
    $this->examRepo->attachQuestionsToAttempt($examAttempt->id, $questionIds);

    return [
        'exam_attempt_id' => $examAttempt->id,
        'exam_data' => $examData
    ];
}


    public function getRandomQuestionsForTrainer(int $trainerId, string $type, int $count = 10)
{
    $exam = Exam::where('trainer_id', $trainerId)
                ->where('type', $type)
                ->firstOrFail();

    $questions = $exam->questions()
                    ->inRandomOrder()
                    ->take($count)
                    ->with(['choices' => function($query) {
                        $query->select('id', 'question_id', 'choice_text');
                    }])
                    ->get()
                    ->map(function ($question) {
                        return [
                            'question_id' => $question->id,
                            'question_text' => $this->extractQuestionText($question->question_text),
                            'question_number' => $this->extractQuestionNumber($question->question_text),
                            'choices' => $question->choices->map(function ($choice) {
                                return [
                                    'choice_id' => $choice->id,
                                    'text' => $choice->choice_text
                                ];
                            })
                        ];
                    });

    return [
        'exam_info' => [
            'exam_id' => $exam->id,
            'total_questions' => $questions->count(),
            'time_limit' => $exam->duration_minutes
        ],
        'questions' => $questions,
        'metadata' => [
            'generated_at' => now()->toIso8601String(),
            'version' => '1.0'
        ]
    ];
}


private function extractQuestionText($text)
{
    return preg_replace('/\(سؤال رقم \d+\)/', '', $text);
}

private function extractQuestionNumber($text)
{
    preg_match('/\(سؤال رقم (\d+)\)/', $text, $matches);
    return $matches[1] ?? null;
}


public function submitExam(int $attemptId, array $answers)
{
    $attempt = ExamAttempt::with('exam')->findOrFail($attemptId); 

    if (!$attempt->started_at) {
        throw new \Exception('لم يتم بدء الامتحان.');
    }

    if ($attempt->finished_at) {
        throw new \Exception('تم تسليم الامتحان مسبقًا.');
    }

    $timeLimit = $attempt->exam->duration_minutes; 
    $timePassed = now()->diffInMinutes($attempt->started_at);

    $isTimeOver = $timePassed > $timeLimit;

    $selectedQuestionIds = ExamAttemptQuestion::where('exam_attempt_id', $attempt->id)
                            ->pluck('question_id')
                            ->toArray();

    $questions = Question::with(['choices' => fn($q) => $q->where('is_correct', true)])
                ->whereIn('id', $selectedQuestionIds)
                ->get();

    $score = 0;
    $results = [];

    foreach ($questions as $q) {
        // نتحقق فقط من الأسئلة التي أجاب عليها الطالب
        if (!isset($answers[$q->id])) {
            continue;
        }

        $userAnswerId = $answers[$q->id];
        $correct = $q->choices->first();
        $isCorrect = $correct && $correct->id == $userAnswerId;

        if ($isCorrect) $score++;

        $results[] = [
            'question_id' => $q->id,
            'user_answer' => $userAnswerId,
            'correct_answer' => $correct->id ?? null,
            'is_correct' => $isCorrect,
            'question_text' => $q->question_text
        ];
    }

    $attempt->update([
        'finished_at' => now(),
        'score' => $score,
    ]);

    return [
        'score' => $score,
        'total' => count($answers), // عدد الأسئلة التي أجاب عليها فقط
        'percentage' => count($answers) > 0 ? round(($score / count($answers)) * 100, 2) : 0,
        'details' => $results,
        'message' => $isTimeOver
            ? 'انتهى وقت الامتحان. تم تصحيح الإجابات المُدخلة فقط.'
            : 'تم تسليم الامتحان بنجاح.'
    ];
}


}
