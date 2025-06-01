<?php

namespace Tests\Feature\Exam;

use App\Models\ExamAttempt;
use App\Models\User;
use App\Models\Student;
use App\Models\Exam;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class StartExamTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
        protected Exam $exam;

    protected Student $student;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::where('email', 'user7@example.com')->firstOrFail();
        $this->student = Student::where('user_id', $this->user->id)->firstOrFail();
        $this->token = JWTAuth::fromUser($this->user);
                $this->exam = Exam::firstOrFail();

    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function student_can_start_exam_attempt_successfully()
    {
        $this->actingAs($this->user);

        $examAttempt = ExamAttempt::create([
            'student_id' => $this->student->id,
            'exam_id' => $this->exam->id,
            'started_at' => null,
        ]);

        $response = $this->postJson('/api/exams/start', [
            'exam_attempt_id' => $examAttempt->id,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'attempt_id',
            'started_at',
        ]);

        $this->assertDatabaseHas('exam_attempts', [
            'id' => $examAttempt->id,
            'started_at' => now(), 
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function returns_422_if_exam_attempt_does_not_exist()
    {
        $this->actingAs($this->user);

        $invalidId = 999999;

        $response = $this->postJson('/api/exams/start', [
            'exam_attempt_id' => $invalidId,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(422);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validation_fails_if_exam_attempt_id_is_missing()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/exams/start', [], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['exam_attempt_id']);
    }
}
