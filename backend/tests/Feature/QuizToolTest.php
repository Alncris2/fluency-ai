<?php

namespace Tests\Feature;

use App\Ai\Agents\EnglishTeacherAgent;
use App\Ai\Tools\QuizTool;
use App\Models\Quiz;
use App\Models\Student;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Redis;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

class QuizToolTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = Student::create([
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'password' => bcrypt('password'),
            'level' => 'intermediate',
            'streak_current' => 3,
        ]);
    }

    public function test_quiz_tool_implements_tool_contract(): void
    {
        $tool = new QuizTool($this->student, 'session-quiz-01');

        $this->assertInstanceOf(Tool::class, $tool);
        $this->assertNotEmpty($tool->description());
    }

    public function test_quiz_tool_is_included_in_agent_tools(): void
    {
        $store = [];
        Redis::shouldReceive('get')->andReturnUsing(function ($key) use (&$store) {
            return $store[$key] ?? null;
        });

        $agent = new EnglishTeacherAgent($this->student, 'session-quiz-01');
        $tools = iterator_to_array($agent->tools());

        $this->assertCount(3, $tools);
        $this->assertInstanceOf(QuizTool::class, $tools[2]);
    }

    public function test_quiz_tool_creates_quiz_record(): void
    {
        $store = [];
        Redis::shouldReceive('get')->andReturnUsing(function ($key) use (&$store) {
            return $store[$key] ?? null;
        });

        $tool = new QuizTool($this->student, 'session-quiz-01');

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')->with('type')->andReturn('multiple_choice');
        $request->shouldReceive('get')->with('topic')->andReturn('Present Simple');
        $request->shouldReceive('get')->with('question')->andReturn('___ she work here?');
        $request->shouldReceive('get')->with('options_json')->andReturn(['Do', 'Does', 'Is', 'Are']);
        $request->shouldReceive('get')->with('correct_answer')->andReturn('Does');
        $request->shouldReceive('get')->with('explanation')->andReturn('Use "does" for he/she/it in Present Simple questions.');

        $result = json_decode($tool->handle($request), true);

        $this->assertArrayHasKey('quiz_id', $result);
        $this->assertEquals('multiple_choice', $result['type']);
        $this->assertDatabaseHas('quizzes', [
            'student_id' => $this->student->id,
            'type' => 'multiple_choice',
            'status' => 'pending',
        ]);
    }

    public function test_quiz_tool_rejects_invalid_type(): void
    {
        $tool = new QuizTool($this->student, 'session-quiz-01');

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')->with('type')->andReturn('invalid_type');

        $result = json_decode($tool->handle($request), true);

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Invalid quiz type', $result['error']);
    }

    public function test_answer_endpoint_correct_answer(): void
    {
        $quiz = Quiz::create([
            'student_id' => $this->student->id,
            'session_id' => 'session-quiz-01',
            'type' => 'multiple_choice',
            'topic' => 'Present Simple',
            'question' => '___ she work here?',
            'options_json' => ['Do', 'Does', 'Is', 'Are'],
            'correct_answer' => 'Does',
            'explanation' => 'Use "does" for he/she/it.',
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/v1/quiz/{$quiz->id}/answer", [
            'answer' => 'Does',
        ]);

        $response->assertOk()
            ->assertJson(['correct' => true, 'score' => 1.0]);

        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'status' => 'answered',
            'student_answer' => 'Does',
        ]);
    }

    public function test_answer_endpoint_wrong_answer(): void
    {
        $quiz = Quiz::create([
            'student_id' => $this->student->id,
            'session_id' => 'session-quiz-01',
            'type' => 'multiple_choice',
            'topic' => 'Present Simple',
            'question' => '___ she work here?',
            'options_json' => ['Do', 'Does', 'Is', 'Are'],
            'correct_answer' => 'Does',
            'explanation' => 'Use "does" for he/she/it.',
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/v1/quiz/{$quiz->id}/answer", [
            'answer' => 'Do',
        ]);

        $response->assertOk()
            ->assertJson(['correct' => false, 'score' => 0.0])
            ->assertJsonStructure(['correct', 'score', 'correct_answer', 'explanation']);
    }

    public function test_answer_endpoint_rejects_already_answered(): void
    {
        $quiz = Quiz::create([
            'student_id' => $this->student->id,
            'session_id' => 'session-quiz-01',
            'type' => 'fill_in_blank',
            'topic' => 'Vocabulary',
            'question' => 'She ___ to school every day.',
            'correct_answer' => 'goes',
            'explanation' => 'Third person singular uses -s/-es.',
            'status' => 'answered',
            'student_answer' => 'goes',
            'score' => 1.0,
            'answered_at' => now(),
        ]);

        $response = $this->postJson("/api/v1/quiz/{$quiz->id}/answer", [
            'answer' => 'goes',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Quiz já foi respondido']);
    }

    public function test_quiz_tool_schema_declares_required_fields(): void
    {
        $tool = new QuizTool($this->student, 'session-quiz-01');

        $typeStub = \Mockery::mock(Type::class);
        $typeStub->shouldReceive('description')->andReturnSelf();
        $typeStub->shouldReceive('nullable')->andReturnSelf();
        $typeStub->shouldReceive('items')->andReturnSelf();

        $schema = \Mockery::mock(JsonSchema::class);
        $schema->shouldReceive('enum')->andReturn($typeStub);
        $schema->shouldReceive('string')->andReturn($typeStub);
        $schema->shouldReceive('array')->andReturn($typeStub);

        $fields = $tool->schema($schema);

        $this->assertIsArray($fields);
        $this->assertArrayHasKey('type', $fields);
        $this->assertArrayHasKey('topic', $fields);
        $this->assertArrayHasKey('question', $fields);
        $this->assertArrayHasKey('correct_answer', $fields);
        $this->assertArrayHasKey('explanation', $fields);
    }
}
