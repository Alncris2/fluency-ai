<?php

namespace Tests\Feature\Api;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPreferencesTest extends TestCase
{
    use RefreshDatabase;

    private array $validPayload = [
        'preferred_name' => 'Ana',
        'goal' => 'viagem',
        'english_level' => 'basico',
        'interests' => ['series', 'musica'],
        'availability' => [
            'days' => ['seg', 'qua'],
            'time_of_day' => ['manha'],
        ],
    ];

    public function test_patch_com_payload_valido_retorna_200_e_persiste_preferences(): void
    {
        $student = Student::factory()->create();

        $response = $this->patchJson("/api/v1/students/{$student->id}/preferences", $this->validPayload);

        $response->assertStatus(200);

        $student->refresh();
        $this->assertEquals($this->validPayload, $student->preferences);
    }

    public function test_patch_sem_goal_retorna_422(): void
    {
        $student = Student::factory()->create();
        $payload = $this->validPayload;
        unset($payload['goal']);

        $this->patchJson("/api/v1/students/{$student->id}/preferences", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['goal']);
    }

    public function test_patch_sem_interests_retorna_422(): void
    {
        $student = Student::factory()->create();
        $payload = $this->validPayload;
        unset($payload['interests']);

        $this->patchJson("/api/v1/students/{$student->id}/preferences", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['interests']);
    }

    public function test_patch_com_goal_invalido_retorna_422(): void
    {
        $student = Student::factory()->create();
        $payload = array_merge($this->validPayload, ['goal' => 'invalido']);

        $this->patchJson("/api/v1/students/{$student->id}/preferences", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['goal']);
    }

    public function test_patch_com_availability_days_vazio_retorna_422(): void
    {
        $student = Student::factory()->create();
        $payload = array_merge($this->validPayload, [
            'availability' => [
                'days' => [],
                'time_of_day' => ['manha'],
            ],
        ]);

        $this->patchJson("/api/v1/students/{$student->id}/preferences", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['availability.days']);
    }
}
