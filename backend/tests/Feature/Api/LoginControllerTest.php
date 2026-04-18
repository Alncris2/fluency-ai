<?php

namespace Tests\Feature\Api;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $credentials = [
        'email' => 'john@example.com',
        'password' => 'password123',
    ];

    public function test_successful_login_returns_200_with_token(): void
    {
        User::factory()->create($this->credentials);

        $response = $this->postJson('/api/auth/login', $this->credentials);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
                'onboarding_completed',
            ]);
    }

    public function test_login_returns_onboarding_completed_false_without_student(): void
    {
        User::factory()->create($this->credentials);

        $response = $this->postJson('/api/auth/login', $this->credentials);

        $response->assertOk()
            ->assertJson(['onboarding_completed' => false]);
    }

    public function test_login_returns_onboarding_completed_false_when_preferences_empty(): void
    {
        User::factory()->create($this->credentials);
        Student::factory()->create([
            'email' => $this->credentials['email'],
            'preferences' => null,
        ]);

        $response = $this->postJson('/api/auth/login', $this->credentials);

        $response->assertOk()
            ->assertJson(['onboarding_completed' => false]);
    }

    public function test_login_returns_onboarding_completed_true_when_preferences_set(): void
    {
        User::factory()->create($this->credentials);
        Student::factory()->create([
            'email' => $this->credentials['email'],
            'preferences' => ['goal' => 'travel', 'interests' => ['music']],
        ]);

        $response = $this->postJson('/api/auth/login', $this->credentials);

        $response->assertOk()
            ->assertJson(['onboarding_completed' => true]);
    }

    public function test_wrong_password_returns_401(): void
    {
        User::factory()->create(['email' => $this->credentials['email']]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $this->credentials['email'],
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials.']);
    }

    public function test_nonexistent_user_returns_401(): void
    {
        $response = $this->postJson('/api/auth/login', $this->credentials);

        $response->assertStatus(401);
    }

    public function test_missing_fields_returns_422(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_invalid_email_format_returns_422(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
