<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\StartExamRequest;
use App\Http\Requests\ShowRandomQuestionsRequest;

use App\Http\Resources\ExamResource;
use App\Http\Requests\SubmitExamRequest;
use Illuminate\Support\Arr;

use App\Services\ExamService;
use App\Services\Interfaces\ExamServiceInterface;

use Illuminate\Http\Request;

class ExamController extends Controller
{
    protected $examService;

    public function __construct(ExamServiceInterface $examService)
    {
        $this->examService = $examService;
    }


public function store(StoreExamRequest $request)
{
    $validated = $request->validated();

    foreach ($validated['questions'] as $i => $q) {
        if (isset($request->questions[$i]['image'])) {
            $validated['questions'][$i]['image'] = $request->questions[$i]['image'];
        }
    }
$trainerId = auth()->user()->trainer->id;
$validated['trainer_id'] = $trainerId;

    $exam = $this->examService->createExamWithQuestions($validated);

    return response()->json([
        'message' => 'تم إنشاء الامتحان بنجاح.',
        'exam' => new ExamResource($exam->load('questions.choices'))
    ], 201);
}

    public function index()
    {
        return response()->json($this->examService->listExams());
    }

public function showByType($type)
{
    $exam = $this->examService->showExam($type);

    if (!$exam) {
        return response()->json([
            'status' => 'error',
            'message' => 'المدرب لا يملك امتحان لهذا النوع'
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'تم جلب بيانات الامتحان بنجاح.',
        'exam' => new ExamResource($exam->load('questions.choices'))
    ]);
}

  public function indexByTrainer($trainerId)
    {
        $exams = $this->examService->listExamsByTrainer($trainerId);
        return response()->json($exams);
    }

    public function showByTrainerAndType($trainerId, $type)
    {
        $exam = $this->examService->showExamByTrainerAndType($trainerId, $type);

        return response()->json([
            'message' => 'تم جلب بيانات الامتحان بنجاح.',
            'exam' => new ExamResource($exam->load('questions.choices'))
        ]);
    }


    public function submit(Request $request, $id)
    {
        $data = $request->validate([
            'answers' => 'required|array',
        ]);
        return response()->json($this->examService->processSubmission($id, $data['answers']));
    }

public function start(StartExamRequest $request)
{
    $examAttemptId = $request->input('exam_attempt_id');

    $attempt = $this->examService->startExamByAttemptId($examAttemptId);

    return response()->json([
        'message' => 'تم بدء الامتحان بنجاح',
        'attempt_id' => $attempt->id,
        'started_at' => $attempt->started_at
    ]);
}






public function showRandomQuestions(ShowRandomQuestionsRequest $request)
{
    $validated = $request->validated();
    $studentId = $request->user()->student->id;

    $questions = $this->examService->getExamQuestionsForStudent($validated['type'], 10, $studentId);

    if (!$questions) {
        return response()->json([
            'message' => '⚠️ يجب إكمال الجلسات التدريبية قبل بدء الامتحان.'
        ], 403);
    }

    return response()->json([
        'questions' => $questions
    ]);
}








public function submitAnswers(SubmitExamRequest $request)
{
   

    try {
        $data = $request->validated();
        $result = $this->examService->submitExam($data['attempt_id'], $data['answers']);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => Arr::except($result, ['message'])
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'لم يتم العثور على محاولة الامتحان'
        ], 404);
    } catch (HttpException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], $e->getStatusCode());
    } catch (\Exception $e) {
        dd($e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء تسليم الامتحان'
        ], 500);
    }
}



 public function evaluate(Request $request)
    {
        $studentId = auth()->user()->student->id;
        $result = $this->examService->evaluateStudent($studentId);
        return response()->json($result);
    }
}
