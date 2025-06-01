<?php

namespace Tests\Feature\Exam;

use App\Models\User;
use App\Models\Student;
use App\Models\Trainer;
use App\Models\Booking;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\TrainingSession;
use App\Models\Car;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShowRandomQuestionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Student $student;
    protected Trainer $trainer;
    protected string $token;
        protected Booking $booking;
  protected TrainingSession $session;
    protected Car $car;

    protected function setUp(): void
    {
        parent::setUp();


        $this->seed(); 

        $this->user = User::where('email', 'user7@example.com')->firstOrFail();

        $this->student = Student::where('user_id', $this->user->id)->firstOrFail();

        $this->trainer = Trainer::firstOrFail();

        $this->token = JWTAuth::fromUser($this->user);
        $this->session = TrainingSession::firstOrFail();
        $this->car = Car::firstOrFail();


          $this->booking = Booking::create([
            'student_id' => $this->student->id,
            'trainer_id' => $this->trainer->id,
            'session_id' => $this->session->id,
            'car_id'     => $this->car->id,
            'status'     => 'booked',
        ]);
    }

        #[\PHPUnit\Framework\Attributes\Test]

    public function student_can_get_random_exam_questions_successfully()
    {
          $this->actingAs($this->student->user);

        $this->booking->update(['status' => 'completed']);
        $response = $this->postJson('/api/generate', [
            'type' => 'driving',
            'trainer_id' => $this->trainer->id,
            'count' => 10,
            'student_id' => $this->student->id,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);
//$response->dump();
        $response->assertStatus(200);

       $response->assertJsonStructure([
    'questions' => [
        'exam_attempt_id',
        'exam_data' => [
            'exam_info' => [
                'exam_id',
                'total_questions',
                'time_limit',
            ],
            'questions' => [
                '*' => [
                    'question_id',
                    'question_text',
                    'question_number',
                    'choices' => [
                        '*' => [
                            'choice_id',
                            'text',
                        ],
                    ],
                ],
            ],
            'metadata' => [
                'generated_at',
                'version',
            ],
        ],
    ]
]);

    }

      #[\PHPUnit\Framework\Attributes\Test]

   public function returns_403_if_student_has_not_completed_sessions()
    {
       
  $this->actingAs($this->student->user);

        $this->booking->update(['status' => 'booked']);
        $response = $this->postJson('/api/generate', [
            'type' => 'driving',
           'trainer_id' => $this->trainer->id,
           'count' => 10,
            'student_id' => $this->student->id,
       ], [
          'Authorization' => "Bearer {$this->token}",
       ]);

      $response->assertStatus(403);
        $response->assertJson([
           'message' => '⚠️ يجب إكمال الجلسات التدريبية قبل بدء الامتحان.'
       ]);
    }

     #[\PHPUnit\Framework\Attributes\Test]

    public function validation_fails_if_type_is_invalid()
    {
        $response = $this->postJson('/api/generate', [
            'type' => 'invalid_type',
            'trainer_id' => $this->trainer->id,
            'count' => 10,
            'student_id' => $this->student->id,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }
}
