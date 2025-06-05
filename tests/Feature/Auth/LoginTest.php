<?php
namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(); 
    $rateLimiter = app(\App\Services\RateLimitService::class);

    $rateLimiter->clear('user1@example.com', 'login');
    }

   #[\PHPUnit\Framework\Attributes\Test]

    public function user_can_login_with_correct_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'user1@example.com',
            'password' => 'password123',
        ]);
 //dd($response->json()); 
       $response->assertStatus(200)->assertJsonStructure([
    'status',
    'message',
    'data' => [
        'user',
        'token',
        'token_type',
        'refresh_token',
        'expires_in',
        'role'
    ],
]);

    }

  #[\PHPUnit\Framework\Attributes\Test]


    public function user_cannot_login_with_wrong_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'user1@example.com',
            'password' => 'wrong-password',
        ]);
 //dd($response->json()); 
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'بيانات الدخول غير صحيحة'
                 ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]

    public function user_is_rate_limited_after_multiple_failed_attempts()
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'user1@example.com',
                'password' => 'wrong-password',
            ]);
        }
 //dd($response->json()); 
       
      $response->assertStatus(429);

$message = $response->json('message');

$this->assertStringContainsString('محاولات كثيرة جدًا', $message);
$this->assertStringContainsString('حاول بعد', $message);


    }
}
