<?php

namespace Tests\Feature\Booking;

use App\Models\User;
use App\Models\Student;
use App\Models\Trainer;
use App\Models\TrainingSession;
use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class BookSessionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Student $student;
    protected Trainer $trainer;
    protected TrainingSession $session;
    protected Car $car;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::where('email', 'user7@example.com')->firstOrFail();
        $this->student = Student::where('user_id', $this->user->id)->firstOrFail();

        $this->trainer = Trainer::firstOrFail();

        $this->session = TrainingSession::where('status', 'available')->firstOrFail();

        $this->car = Car::where('status', 'available')->firstOrFail();

        $this->token = JWTAuth::fromUser($this->user);
    }

  #[\PHPUnit\Framework\Attributes\Test]
public function student_can_book_a_session_successfully()
{
    $response = $this->postJson('/api/bookings', [
        'session_id' => $this->session->id,
        'car_id' => $this->car->id,
    ], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'status',
                'student' => [
                    'id',
                    'name',
                ],
                'trainer' => [
                    'id',
                    'name',
                ],
                'car' => [
                    'id',
                    'model',
                    'transmission',
                ],
                'session' => [
                    'id',
                    'date',
                    'start_time',
                    'end_time',
                ],
                'created_at',
            ],
        ]);

    // dd($response->json());

    $responseData = $response->json();

    $this->assertDatabaseHas('bookings', [
        'student_id' => $this->student->id,
        'session_id' => $this->session->id,
        'car_id' => $this->car->id,
        'status' => 'booked',
    ]);

    $this->assertDatabaseHas('training_sessions', [
        'id' => $this->session->id,
        'status' => 'booked',
    ]);

    $this->assertDatabaseHas('cars', [
        'id' => $this->car->id,
        'status' => 'booked',
    ]);
}


     #[\PHPUnit\Framework\Attributes\Test]
   public function booking_fails_if_session_is_not_available()
    {
        $this->session->update(['status' => 'booked']);

        $response = $this->postJson('/api/bookings', [
            'session_id' => $this->session->id,
            'car_id' => $this->car->id,
        ], [
         'Authorization' => "Bearer {$this->token}",
        ]);
       // dd($response->json());


        $response->assertStatus(422)
            ->assertJsonValidationErrors(['session']);
     }

    #[\PHPUnit\Framework\Attributes\Test]
     public function booking_fails_if_car_is_not_available()
     {
        $car = \App\Models\Car::find($this->car->id);
$car->status = 'booked';
$car->save();
    $car->refresh();

    $this->assertEquals('booked', $car->status);
     

        $response = $this->postJson('/api/bookings', [
             'session_id' => $this->session->id,
            'car_id' => $this->car->id,
         ], [
            'Authorization' => "Bearer {$this->token}",
         ]);
      //  dd($response->json());

        $response->assertStatus(422)
             ->assertJsonValidationErrors(['car']);
    }
}
