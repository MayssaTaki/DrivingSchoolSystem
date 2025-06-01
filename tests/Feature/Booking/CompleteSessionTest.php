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

class CompleteSessionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Student $student;
    protected Trainer $trainer;
    protected TrainingSession $session;
    protected Car $car;
    protected Booking $booking;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::where('email', 'user7@example.com')->firstOrFail();
        $this->student = Student::where('user_id', $this->user->id)->firstOrFail();

        $this->trainer = Trainer::firstOrFail();

        $this->car = Car::firstOrFail();
        $this->car->status = 'booked';
        $this->car->save();

        $this->session = TrainingSession::firstOrFail();
        $this->session->status = 'booked';
        $this->session->save();

        $this->booking = Booking::create([
            'student_id' => $this->student->id,
            'trainer_id' => $this->trainer->id,
            'session_id' => $this->session->id,
            'car_id' => $this->car->id,
            'status' => 'started',
        ]);

        $this->token = JWTAuth::fromUser($this->trainer->user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function trainer_can_complete_session_successfully()
    {
        $response = $this->postJson(
            "/api/bookings/{$this->booking->id}/complete",
            [],
            ['Authorization' => "Bearer {$this->token}"]
        );

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'تم إنهاء الجلسة بنجاح. .',
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('training_sessions', [
            'id' => $this->session->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('cars', [
            'id' => $this->car->id,
            'status' => 'available',
        ]);
    }

     #[\PHPUnit\Framework\Attributes\Test]
    public function cannot_complete_session_if_not_started()
    {
        $this->actingAs($this->trainer->user);

        $this->booking->update(['status' => 'booked']);

        $response = $this->postJson(
            "/api/bookings/{$this->booking->id}/complete",
            [],
            ['Authorization' => "Bearer {$this->token}"]
        );

        $response->assertStatus(500)
            ->assertJson([
                'status' => false,
                'message' => 'حدث خطأ أثناء إنهاء الجلسة.',
            ]);
    }

}
