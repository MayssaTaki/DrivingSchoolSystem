<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\StartExamRequest;

use App\Http\Resources\ExamResource;
use App\Http\Requests\SubmitExamRequest;

use App\Services\ExamService;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    protected $examService;

    public function __construct(ExamService $examService)
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

   public function show($id)
{
    $exam = $this->examService->showExam($id);

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
    $studentId = auth()->user()->student->id;

    $examId = $request->input('exam_id');

    $attempt = $this->examService->startExam($examId, $studentId);

    return response()->json([
        'message' => 'تم بدء الامتحان بنجاح.',
        'attempt_id' => $attempt->id,
        'started_at' => $attempt->started_at
    ], 200);
}


public function submitAnswers(SubmitExamRequest $request)
{
    $data = $request->validated();

    $result = $this->examService->submitExam($data['attempt_id'], $data['answers']);

    return response()->json([
        'message' => 'تم تصحيح الامتحان بنجاح.',
        'result' => $result
    ]);
}
}
