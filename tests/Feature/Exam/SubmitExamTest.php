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
use Carbon\Carbon;

class SubmitExamTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Student $student;
    protected Exam $exam;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::where('email', 'user7@example.com')->firstOrFail();
        $this->student = Student::where('user_id', $this->user->id)->firstOrFail();
        $this->token = JWTAuth::fromUser($this->user);
        $this->exam = Exam::firstOrFail();
        $this->exam->update(['duration_minutes' => 30]);
        $this->token = auth('api')->login($this->user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function exam_submission_with_expired_time_and_partial_answers()
    {
        $examStart = Carbon::parse('2023-01-01 10:00:00');
        $examEnd = $examStart->copy()->addMinutes(30);

        Carbon::setTestNow($examEnd->copy()->addMinutes(11));

        $examAttempt = ExamAttempt::create([
            'student_id' => $this->student->id,
            'exam_id' => $this->exam->id,
            'started_at' => $examStart,
        ]);

        $questions = $this->exam->questions()->take(10)->get();

        foreach ($questions as $question) {
            ExamAttemptQuestion::create([
                'exam_attempt_id' => $examAttempt->id,
                'question_id' => $question->id,
            ]);
        }

        $answeredCount = 5;
        $answers = [];

        foreach ($questions->take($answeredCount) as $question) {
            $correctChoice = $question->choices()->where('is_correct', true)->first();
            if ($correctChoice) {
                $answers[$question->id] = $correctChoice->id;
            }
        }

        $response = $this->actingAs($this->user, 'api')->postJson('/api/exams/submit', [
            'attempt_id' => $examAttempt->id,
            'answers' => $answers,
        ]);

        $response->dump();

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'score' => $answeredCount,
                'total' => $answeredCount, 
            ]
        ]);

        $this->assertStringContainsString(
            'انتهى وقت الامتحان',
            $response->json('message') ?? $response->json('data.message') // يعتمد على مكان الرسالة
        );

        $this->assertNotNull($examAttempt->fresh()->finished_at);
        $this->assertTrue($examAttempt->fresh()->is_time_over ?? true);

        Carbon::setTestNow();
    }


    #[\PHPUnit\Framework\Attributes\Test]
public function test_successful_exam_submission_with_all_answers()
{
    $examStart = Carbon::now()->subMinutes(5); 
    Carbon::setTestNow($examStart->copy()->addMinutes(10)); 

    $examAttempt = ExamAttempt::create([
        'student_id' => $this->student->id,
        'exam_id' => $this->exam->id,
        'started_at' => $examStart,
    ]);

    $questions = $this->exam->questions()->take(10)->get();

    foreach ($questions as $question) {
        ExamAttemptQuestion::create([
            'exam_attempt_id' => $examAttempt->id,
            'question_id' => $question->id,
        ]);
    }

    $answers = [];

    foreach ($questions as $question) {
        $correctChoice = $question->choices()->where('is_correct', true)->first();
        if ($correctChoice) {
            $answers[$question->id] = $correctChoice->id;
        }
    }

    $response = $this->actingAs($this->user, 'api')->postJson('/api/exams/submit', [
        'attempt_id' => $examAttempt->id,
        'answers' => $answers,
    ]);

    $response->assertStatus(200);

    $response->assertJson([
    'success' => true,
    'message' => 'تم تسليم الامتحان بنجاح.',
    'data' => [
        'score' => 10,
        'total' => 10,
        'percentage' => 100,
    ],
]);


    $this->assertNotNull($examAttempt->fresh()->finished_at);
    $this->assertEquals(count($answers), $examAttempt->fresh()->score);

    Carbon::setTestNow(); 
}

}  