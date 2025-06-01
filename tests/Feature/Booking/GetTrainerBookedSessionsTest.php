<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\User;
use App\Models\Trainer;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class GetTrainerBookedSessionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $trainerUser;
    protected Trainer $trainer;
    protected Student $student;
    protected TrainingSession $session;
    protected Car $car;
    protected Booking $bookingBooked;
    protected Booking $bookingOtherStatus;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->trainer = Trainer::firstOrFail();
        $this->trainerUser = $this->trainer->user;
        $this->token = JWTAuth::fromUser($this->trainerUser);

        $this->student = Student::firstOrFail();

        $this->session = TrainingSession::firstOrFail();
        $this->session->status = 'booked';
        $this->session->save();

        $this->car = Car::firstOrFail();
        $this->car->status = 'booked';
        $this->car->save();

        $this->bookingBooked = Booking::create([
            'student_id' => $this->student->id,
            'trainer_id' => $this->trainer->id,
            'session_id' => $this->session->id,
            'car_id'     => $this->car->id,
            'status'     => 'booked',
        ]);

        $this->bookingOtherStatus = Booking::create([
            'student_id' => $this->student->id,
            'trainer_id' => $this->trainer->id,
            'session_id' => $this->session->id,
            'car_id'     => $this->car->id,
            'status'     => 'completed',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function trainer_can_view_only_booked_sessions()
    {
        $response = $this->getJson(
            '/api/trainer/bookings',
            ['Authorization' => "Bearer {$this->token}"]
        );

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonFragment([
            'id' => $this->bookingBooked->id,
            'status' => 'booked',
            'trainer' => [
                'id' => $this->trainer->id,
                'name' => $this->trainerUser->name,
            ],
        ]);

        $response->assertJsonStructure([
            'data' => [
                [
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
                ]
            ]
        ]);
    }
}
