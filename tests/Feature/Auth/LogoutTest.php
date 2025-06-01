<?php
namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Redis;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();
     
        $this->seed();

        $this->user = User::where('email', 'user1@example.com')->first();

        $this->token = JWTAuth::fromUser($this->user);

        Redis::flushall();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_logout_successfully()
    {
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'تم تسجيل الخروج بنجاح',
            ]);

        $exists = Redis::exists("blacklist:{$this->token}");
        $this->assertTrue($exists > 0, "Token should be blacklisted in Redis after logout");
    }
}
