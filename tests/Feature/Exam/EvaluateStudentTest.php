<?php

namespace Tests\Feature\Exam;

use App\Models\User;
use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class EvaluateStudentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Student $student;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::where('email', 'user7@example.com')->firstOrFail();
        $this->student = Student::where('user_id', $this->user->id)->firstOrFail();
        $this->trainer = \App\Models\Trainer::firstOrFail();

        $question = \App\Models\Question::inRandomOrder()->take(10)->get();
        $questionIds = $question->pluck('id')->toArray();

        $examTypes = Exam::distinct()->pluck('type');

        if (!$examTypes->contains('mechanics')) {
            Exam::create([
                'type' => 'mechanics',
                'title' => 'امتحان نظرية',
                'description' => 'امتحان النظرية الأساسي',
                'trainer_id' => $this->trainer->id,
            ]);
        }

        if (!$examTypes->contains('driving')) {
            Exam::create([
                'type' => 'driving',
                'title' => 'امتحان عملية',
                'description' => 'امتحان العملية الأساسي',
                'trainer_id' => $this->trainer->id,
            ]);
        }

        $examTheory = Exam::where('type', 'mechanics')->first();
        $examPractical = Exam::where('type', 'driving')->first();

        $attemptTheory = ExamAttempt::create([
            'student_id' => $this->student->id,
            'exam_id' => $examTheory->id,
            'score' => 8,
            'finished_at' => now()->subDay(),
        ]);

        for ($i = 1; $i <= 10; $i++) {
            ExamAttemptQuestion::create([
                'exam_attempt_id' => $attemptTheory->id,
                'question_text' => "سؤال رقم $i",
                'answer' => 'الإجابة الصحيحة',
                'question_id' => $questionIds[$i - 1] ?? null,
            ]);
        }

        $attemptPractical = ExamAttempt::create([
            'student_id' => $this->student->id,
            'exam_id' => $examPractical->id,
            'score' => 4,
            'started_at' => now(),
            'finished_at' => now()->subDays(2),
        ]);

        for ($i = 1; $i <= 10; $i++) {
            ExamAttemptQuestion::create([
                'exam_attempt_id' => $attemptPractical->id,
                'question_text' => "سؤال رقم $i",
                'answer' => 'الإجابة الصحيحة',
                'question_id' => $questionIds[$i - 1] ?? null,
            ]);
        }

        $this->token = JWTAuth::fromUser($this->user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_student_evaluation_returns_correct_status()
    {
        ExamAttempt::where('student_id', $this->student->id)->update(['score' => 10]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}"
        ])->getJson('/api/student/evaluation');

        $response->assertStatus(200);
      $responseData = $response->json();

$this->assertEquals($this->student->id, $responseData['student_id']);
$this->assertFalse($responseData['passed_all']);
$this->assertEquals('❌ الطالب لم يجتز كل الفحوصات', $responseData['final_status']);

$examTypes = Exam::distinct()->pluck('type')->toArray();
$typesInResponse = array_map(fn($d) => $d['type'], $responseData['details']);
foreach ($examTypes as $type) {
    $this->assertContains($type, $typesInResponse);
}

foreach ($responseData['details'] as $detail) {
    $this->assertArrayHasKey('type', $detail);
    $this->assertArrayHasKey('status', $detail);

    $this->assertTrue(
        array_key_exists('score', $detail),
        "Missing 'score' in detail: " . json_encode($detail)
    );
    $this->assertTrue(
        array_key_exists('percentage', $detail),
        "Missing 'percentage' in detail: " . json_encode($detail)
    );

    $this->assertTrue(
        is_numeric($detail['score']) || is_null($detail['score']),
        "Score should be numeric or null, got: " . var_export($detail['score'], true)
    );

    $this->assertTrue(
        is_numeric($detail['percentage']) || is_null($detail['percentage']),
        "Percentage should be numeric or null, got: " . var_export($detail['percentage'], true)
    );
}
    }
}
