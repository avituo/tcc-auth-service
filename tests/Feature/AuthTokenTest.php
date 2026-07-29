<?php

namespace Tests\Feature;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTokenTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-secret-with-at-least-thirty-two-bytes';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('jwt.secret', self::SECRET);
        config()->set('jwt.issuer', 'https://auth.example.test');
        config()->set('jwt.audience', 'tcc-api-gateway');
        config()->set('jwt.ttl_minutes', 15);
    }

    public function test_it_issues_a_complete_jwt_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
            'password' => 'correct-password',
            'roles' => ['admin', 'user'],
        ]);

        $response = $this->postJson('/api/v1/auth/token', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('expires_in', 900);

        $claims = JWT::decode($response->json('access_token'), new Key(self::SECRET, 'HS256'));

        $this->assertSame((string) $user->id, $claims->sub);
        $this->assertSame('ada@example.test', $claims->email);
        $this->assertSame('Ada Lovelace', $claims->name);
        $this->assertSame(['admin', 'user'], $claims->roles);
        $this->assertSame('https://auth.example.test', $claims->iss);
        $this->assertSame('tcc-api-gateway', $claims->aud);
        $this->assertNotEmpty($claims->jti);
        $this->assertSame($claims->iat, $claims->nbf);
        $this->assertSame(900, $claims->exp - $claims->iat);
    }

    public function test_it_rejects_invalid_credentials_without_disclosing_the_reason(): void
    {
        User::factory()->create(['email' => 'ada@example.test']);

        $this->postJson('/api/v1/auth/token', [
            'email' => 'ada@example.test',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertExactJson(['message' => 'Invalid credentials']);

        $this->postJson('/api/v1/auth/token', [
            'email' => 'missing@example.test',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertExactJson(['message' => 'Invalid credentials']);
    }

    public function test_it_validates_the_token_request(): void
    {
        $this->postJson('/api/v1/auth/token', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_it_throttles_repeated_login_attempts(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/auth/token', [
                'email' => 'attacker@example.test',
                'password' => 'wrong-password',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/auth/token', [
            'email' => 'attacker@example.test',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }
}
