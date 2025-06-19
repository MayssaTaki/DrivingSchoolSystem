<?php
namespace App\Services;
use App\Services\Interfaces\TransactionServiceInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Exam;
use App\Models\Question;
use Carbon\Carbon;
use App\Events\ImageUploaded;
use App\Exceptions\ExamException;

use App\Models\ExamAttemptQuestion;
use App\Services\Interfaces\ExamServiceInterface;
use App\Services\Interfaces\ActivityLoggerServiceInterface;
use App\Services\Interfaces\LogServiceInterface;

use App\Models\ExamAttempt;

use App\Repositories\Contracts\ExamRepositoryInterface;

class ExamService implements ExamServiceInterface
{
    protected $examRepo;
        protected $transactionService;

   protected ActivityLoggerServiceInterface $activityLogger;
    protected LogServiceInterface $logService;
    public function __construct(ExamRepositoryInterface $examRepo,
    ActivityLoggerServiceInterface $activityLogger,
        LogServiceInterface $logService,        TransactionServiceInterface $transactionService,
)
    {
        $this->examRepo = $examRepo;
          $this->activityLogger = $activityLogger;
        $this->logService = $logService;
                $this->transactionService = $transactionService;

    }

    protected function checkTrainerApproval($trainer)
{
    if ($trainer->status !== 'approved') {
        throw new ExamException("لا يمكن إنشاء امتحان  لأن حالة حسابك غير معتمدة.", 403);
    }
}


public function createExamWithQuestions(array $data)
{ 
    $trainer = auth()->user()->trainer;
    try {
        return $this->transactionService->run(function () use ($data, $trainer) {
                        $this->checkTrainerApproval($trainer);
            $exam = $this->examRepo->createExamWithQuestions($data);
     foreach ($exam->questions as $question) {
                if ($question->image_path) {
                    $fullPath = storage_path('app/public/' . $question->image_path);
                    event(new ImageUploaded($fullPath));
                }
            }
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

    public function showExam($type)
    {
        return $this->examRepo->getExamWithQuestions($type);
    }
 public function listExamsByTrainer($trainerId)
    {
        return $this->examRepo->getAllExamsByTrainerId($trainerId);
    }

    public function showExamByTrainerAndType($trainerId, $type)
    {
        return $this->examRepo->getExamWithQuestionsByTrainerAndType($trainerId, $type);
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





public function getExamQuestionsForStudent(string $type, int $count = 10, int $studentId)
{
    $trainerId = $this->examRepo->hasCompletedSessions($studentId);

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


public function extractQuestionText($text)
{
    return preg_replace('/\(سؤال رقم \d+\)/', '', $text);
}

public function extractQuestionNumber($text)
{
    preg_match('/\(سؤال رقم (\d+)\)/', $text, $matches);
    return $matches[1] ?? null;
}


public function submitExam(int $attemptId, array $answers)
{
    try {
        return $this->transactionService->run(function () use ($attemptId, $answers) {

            $attempt = ExamAttempt::with('exam')->findOrFail($attemptId); 

            if (!$attempt->started_at) {
                throw new \Exception('لم يتم بدء الامتحان.');
            }

            if ($attempt->finished_at) {
                throw new \Exception('تم تسليم الامتحان مسبقًا.');
            }

$endTime = \Carbon\Carbon::parse($attempt->started_at)->addMinutes($attempt->exam->duration_minutes);
$isTimeOver = $endTime->lt(now());
$isTimeOver = $endTime->copy()->addSeconds(5)->lt(now());

            $selectedQuestionIds = ExamAttemptQuestion::where('exam_attempt_id', $attempt->id)
                                    ->pluck('question_id')
                                    ->toArray();

            $questions = Question::with(['choices' => fn($q) => $q->where('is_correct', true)])
                        ->whereIn('id', $selectedQuestionIds)
                        ->get();

            $score = 0;
            $results = [];

            foreach ($questions as $q) {
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

            $this->activityLogger->log(
                'تسليم محاولة امتحان',
                [
                    'exam_id' => $attempt->exam_id,
                    'student_id' => $attempt->student_id,
                    'score' => $score,
                    'finished_at' => $attempt->finished_at,
                    'time_over' => $isTimeOver,
                ],
                'exam_attempts',
                $attempt,
                auth()->user(),
                'submit'
            );

            $response = [
                'score' => $score,
                'total' => count($answers), 
                'percentage' => count($answers) > 0 ? round(($score / count($answers)) * 100, 2) : 0,
                'details' => $results,
                'message' => $isTimeOver
                    ? 'انتهى وقت الامتحان. تم تصحيح الإجابات المُدخلة فقط.'
                    : 'تم تسليم الامتحان بنجاح.'
            ];

            return $response;
        });
    } catch (\Exception $e) {
        $this->logService->log(
            'error',
            'فشل تسليم محاولة الامتحان',
            [
                'message' => $e->getMessage(),
                'exam_attempt_id' => $attemptId,
                'trace' => $e->getTraceAsString(),
            ],
            'exam_attempts'
        );

        throw $e;
    }


}







public function evaluateStudent(int $studentId, float $passPercentage = 60.0): array
{
    $requiredTypes = Exam::distinct()->pluck('type')->toArray();

    $result = [
        'student_id' => $studentId,
        'details' => [],
        'passed_all' => true,
    ];

    foreach ($requiredTypes as $type) {
        $attempt = ExamAttempt::with('exam')
            ->where('student_id', $studentId)
            ->whereNotNull('finished_at')
            ->whereHas('exam', fn($q) => $q->where('type', $type))
            ->latest('finished_at')
            ->first();

       if (!$attempt) {
    $result['details'][] = [
        'type' => $type,
        'score' => null,
        'percentage' => null,
        'status' => '❌ لم يتم إجراء الامتحان'
    ];
    $result['passed_all'] = false;
    continue;
}


        $totalQuestions = ExamAttemptQuestion::where('exam_attempt_id', $attempt->id)->count();
        $percentage = $totalQuestions > 0 ? round(($attempt->score / $totalQuestions) * 100, 2) : 0;
        $isPassed = $percentage >= $passPercentage;

        $result['details'][] = [
            'type' => $type,
            'score' => $attempt->score,
            'percentage' => $percentage,
            'status' => $isPassed ? '✅ ناجح' : '❌ راسب'
        ];

        if (!$isPassed) {
            $result['passed_all'] = false;
        }
    }

    $result['final_status'] = $result['passed_all']
        ? '✅ الطالب ناجح في كل الفحوصات'
        : '❌ الطالب لم يجتز كل الفحوصات';

    return $result;
}



}
