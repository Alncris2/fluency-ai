<?php

namespace Tests\Feature;

use App\Ai\Agents\EnglishTeacherAgent;
use App\Ai\Tools\GetStudentProgressTool;
use App\Ai\Tools\SaveLearningNoteTool;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

class EnglishTeacherAgentTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = Student::create([
            'name' => 'Maria',
            'email' => 'maria@example.com',
            'level' => 'beginner',
            'subscription_plan' => 'free',
            'streak_current' => 3,
            'streak_best' => 7,
        ]);
    }

    public function test_agent_implements_correct_contracts(): void
    {
        $agent = new EnglishTeacherAgent($this->student, 'session-001');

        $this->assertInstanceOf(Agent::class, $agent);
        $this->assertInstanceOf(Conversational::class, $agent);
        $this->assertInstanceOf(HasTools::class, $agent);
    }

    public function test_instructions_contain_apa_phases(): void
    {
        $agent = new EnglishTeacherAgent($this->student, 'session-001');

        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('ADQUIRIR', $instructions);
        $this->assertStringContainsString('PRATICAR', $instructions);
        $this->assertStringContainsString('AJUSTAR', $instructions);
        $this->assertStringContainsString('30%', $instructions);
        $this->assertStringContainsString('50%', $instructions);
        $this->assertStringContainsString('20%', $instructions);
    }

    public function test_instructions_contain_student_name_and_level(): void
    {
        $agent = new EnglishTeacherAgent($this->student, 'session-001');

        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('Maria', $instructions);
        $this->assertStringContainsString('beginner', $instructions);
    }

    public function test_voice_mode_instructions_are_concise(): void
    {
        $agent = new EnglishTeacherAgent($this->student, 'session-001', voiceMode: true);

        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('VOICE MODE', $instructions);
        $this->assertStringContainsString('1-2 short', $instructions);
        $this->assertStringNotContainsString('ADQUIRIR', $instructions);
    }

    public function test_tools_include_expected_tools(): void
    {
        $agent = new EnglishTeacherAgent($this->student, 'session-001');

        $tools = iterator_to_array($agent->tools());

        $this->assertCount(2, $tools);
        $this->assertContainsOnlyInstancesOf(Tool::class, $tools);
        $this->assertInstanceOf(GetStudentProgressTool::class, $tools[0]);
        $this->assertInstanceOf(SaveLearningNoteTool::class, $tools[1]);
    }

    public function test_history_is_stored_and_retrieved_from_redis(): void
    {
        $store = [];

        Redis::shouldReceive('get')
            ->andReturnUsing(function ($key) use (&$store) {
                return $store[$key] ?? null;
            });

        Redis::shouldReceive('setex')
            ->andReturnUsing(function ($key, $ttl, $value) use (&$store) {
                $store[$key] = $value;
            });

        $agent = new EnglishTeacherAgent($this->student, 'session-redis-test');
        $agent->appendToHistory('user', 'Hello teacher');
        $agent->appendToHistory('assistant', 'Hello Maria!');

        $messages = iterator_to_array($agent->messages());

        $this->assertCount(2, $messages);
        $this->assertEquals('Hello teacher', $messages[0]->content);
        $this->assertEquals('Hello Maria!', $messages[1]->content);
    }

    public function test_voice_greeting_includes_student_name(): void
    {
        $agent = new EnglishTeacherAgent($this->student, 'greeting');

        $greeting = $agent->getVoiceGreeting();

        $this->assertStringContainsString('Maria', $greeting);
    }

    public function test_voice_greeting_mentions_streak_when_active(): void
    {
        $agent = new EnglishTeacherAgent($this->student, 'greeting');

        $greeting = $agent->getVoiceGreeting();

        $this->assertStringContainsString('Day 3', $greeting);
    }

    public function test_get_student_progress_tool(): void
    {
        $tool = new GetStudentProgressTool($this->student);
        $request = new Request([]);

        $result = json_decode($tool->handle($request), true);

        $this->assertEquals('Maria', $result['name']);
        $this->assertEquals('beginner', $result['level']);
        $this->assertEquals(3, $result['streak']);
    }

    public function test_save_learning_note_tool_persists_to_database(): void
    {
        $tool = new SaveLearningNoteTool($this->student);
        $request = new Request([
            'type' => 'mistake',
            'content' => 'Confuses present simple and present continuous',
            'apa_phase' => 'practice',
            'importance' => 3,
        ]);

        $result = json_decode($tool->handle($request), true);

        $this->assertTrue($result['saved']);
        $this->assertDatabaseHas('memories', [
            'student_id' => $this->student->id,
            'type' => 'mistake',
            'apa_phase' => 'practice',
            'importance' => 3,
        ]);
    }

    public function test_chat_endpoint_returns_message(): void
    {
        $store = [];
        Redis::shouldReceive('get')->andReturnUsing(function ($key) use (&$store) {
            return $store[$key] ?? null;
        });
        Redis::shouldReceive('setex')->andReturnUsing(function ($key, $ttl, $value) use (&$store) {
            $store[$key] = $value;
        });

        EnglishTeacherAgent::fake(['Great question, Maria!']);

        $response = $this->postJson("/api/v1/students/{$this->student->id}/chat", [
            'message' => 'What is present simple?',
            'session_id' => 'test-session',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'session_id']);
    }

    public function test_voice_greeting_endpoint(): void
    {
        $response = $this->getJson("/api/v1/students/{$this->student->id}/chat/voice/greeting");

        $response->assertOk()
            ->assertJsonStructure(['greeting']);

        $this->assertStringContainsString('Maria', $response->json('greeting'));
    }
}
