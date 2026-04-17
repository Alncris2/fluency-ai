<?php

namespace Tests\Unit\Services;

use App\Ai\Agents\EnglishTeacherAgent;
use App\Models\Conversation;
use App\Models\LessonPlan;
use App\Models\Memory;
use App\Models\Student;
use App\Services\MemoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class MemoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private MemoryService $service;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new MemoryService;

        $this->student = Student::create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'level' => 'intermediate',
            'subscription_plan' => 'premium',
            'streak_current' => 5,
            'streak_best' => 10,
        ]);
    }

    // ── buildContext ──────────────────────────────────────────────────────────

    public function test_build_context_with_plan_and_memories(): void
    {
        LessonPlan::create([
            'student_id' => $this->student->id,
            'current_unit' => 3,
            'current_lesson' => 7,
            'sessions_per_week' => 4,
            'weak_areas' => ['present perfect', 'conditionals'],
        ]);

        Memory::create([
            'student_id' => $this->student->id,
            'type' => 'mistake',
            'content' => 'Confuses present simple and present continuous',
            'importance' => 4,
        ]);

        $this->student->setRelation('learningPlan', $this->student->fresh()->learningPlan ?? LessonPlan::where('student_id', $this->student->id)->first());

        $context = $this->service->buildContext($this->student);

        $this->assertStringContainsString('João Silva', $context);
        $this->assertStringContainsString('intermediate', $context);
        $this->assertStringContainsString('premium', $context);
        $this->assertStringContainsString('Plano de Estudos', $context);
        $this->assertStringContainsString('Memórias Recentes', $context);
        $this->assertStringContainsString('[mistake]', $context);
        $this->assertStringContainsString('Confuses present simple', $context);
    }

    public function test_build_context_without_plan(): void
    {
        Memory::create([
            'student_id' => $this->student->id,
            'type' => 'achievement',
            'content' => 'Completed past tense unit',
            'importance' => 3,
        ]);

        $context = $this->service->buildContext($this->student);

        $this->assertStringContainsString('João Silva', $context);
        $this->assertStringContainsString('Perfil', $context);
        $this->assertStringNotContainsString('Plano de Estudos', $context);
        $this->assertStringContainsString('Memórias Recentes', $context);
    }

    public function test_build_context_without_memories(): void
    {
        LessonPlan::create([
            'student_id' => $this->student->id,
            'current_unit' => 1,
            'current_lesson' => 1,
            'sessions_per_week' => 3,
            'weak_areas' => [],
        ]);

        $this->student->setRelation('learningPlan', LessonPlan::where('student_id', $this->student->id)->first());

        $context = $this->service->buildContext($this->student);

        $this->assertStringContainsString('Perfil', $context);
        $this->assertStringContainsString('Plano de Estudos', $context);
        $this->assertStringNotContainsString('Memórias Recentes', $context);
    }

    // ── saveSessionHistory / getSessionHistory ────────────────────────────────

    public function test_save_and_get_session_history_roundtrip(): void
    {
        $messages = [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!'],
        ];

        $encoded = json_encode($messages);
        $capturedTtl = null;

        Redis::shouldReceive('setex')
            ->once()
            ->andReturnUsing(function (string $key, int $ttl, string $value) use (&$capturedTtl): void {
                $capturedTtl = $ttl;
            });

        Redis::shouldReceive('get')
            ->once()
            ->andReturn($encoded);

        $this->service->saveSessionHistory($this->student->id, 'session-abc', $messages);
        $result = $this->service->getSessionHistory($this->student->id, 'session-abc');

        $this->assertEquals(3600 * 24, $capturedTtl);
        $this->assertCount(2, $result);
        $this->assertEquals('user', $result[0]['role']);
        $this->assertEquals('Hello', $result[0]['content']);
        $this->assertEquals('assistant', $result[1]['role']);
    }

    public function test_get_session_history_returns_empty_array_for_missing_key(): void
    {
        Redis::shouldReceive('get')
            ->once()
            ->andReturn(null);

        $result = $this->service->getSessionHistory($this->student->id, 'nonexistent-session');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // ── persistConversation ───────────────────────────────────────────────────

    public function test_persist_conversation_creates_new_record(): void
    {
        $messages = [
            ['role' => 'user', 'content' => 'What is the past tense of "go"?'],
            ['role' => 'assistant', 'content' => 'The past tense of "go" is "went".'],
        ];

        $conversation = $this->service->persistConversation($this->student, 'session-001', $messages);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals('session-001', $conversation->session_id);
        $this->assertEquals($this->student->id, $conversation->student_id);
        $this->assertCount(2, $conversation->messages);

        $this->assertDatabaseHas('conversations', [
            'session_id' => 'session-001',
            'student_id' => $this->student->id,
            'session_type' => 'chat',
        ]);
    }

    public function test_persist_conversation_updates_existing_record(): void
    {
        $initial = [['role' => 'user', 'content' => 'Hello']];
        $this->service->persistConversation($this->student, 'session-002', $initial);

        $updated = [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi!'],
            ['role' => 'user', 'content' => 'How are you?'],
        ];
        $conversation = $this->service->persistConversation($this->student, 'session-002', $updated);

        $this->assertCount(3, $conversation->messages);
        $this->assertDatabaseCount('conversations', 1);
        $this->assertEquals('session-002', $conversation->session_id);
    }

    // ── summarizeAndSave ──────────────────────────────────────────────────────

    public function test_summarize_and_save_returns_null_when_history_is_empty(): void
    {
        Redis::shouldReceive('get')
            ->once()
            ->andReturn(null);

        $result = $this->service->summarizeAndSave($this->student, 'empty-session');

        $this->assertNull($result);
    }

    public function test_summarize_and_save_creates_memory_when_history_exists(): void
    {
        $messages = [
            ['role' => 'user', 'content' => 'I goes to school yesterday.'],
            ['role' => 'assistant', 'content' => 'Great try! We say "I went to school yesterday."'],
        ];

        // Redis::get may be called multiple times: once for session history,
        // once internally by EnglishTeacherAgent::messages() for its own history.
        Redis::shouldReceive('get')
            ->andReturn(json_encode($messages));

        EnglishTeacherAgent::fake(['[mistake] Student confused irregular verb "go" — used "goes" instead of "went" in past tense.']);

        $memory = $this->service->summarizeAndSave($this->student, 'session-summary');

        $this->assertInstanceOf(Memory::class, $memory);
        $this->assertEquals('mistake', $memory->type);
        $this->assertEquals($this->student->id, $memory->student_id);
        $this->assertEquals(3, $memory->importance);

        $this->assertDatabaseHas('memories', [
            'student_id' => $this->student->id,
            'type' => 'mistake',
        ]);
    }

    // ── getRelevantMemories ───────────────────────────────────────────────────

    public function test_get_relevant_memories_orders_by_importance_desc(): void
    {
        Memory::create(['student_id' => $this->student->id, 'type' => 'mistake', 'content' => 'Low importance', 'importance' => 1]);
        Memory::create(['student_id' => $this->student->id, 'type' => 'achievement', 'content' => 'High importance', 'importance' => 5]);
        Memory::create(['student_id' => $this->student->id, 'type' => 'vocabulary', 'content' => 'Medium importance', 'importance' => 3]);

        $memories = $this->service->getRelevantMemories($this->student);

        $this->assertInstanceOf(Collection::class, $memories);
        $this->assertCount(3, $memories);
        $this->assertEquals(5, $memories->first()->importance);
        $this->assertEquals(1, $memories->last()->importance);
    }

    public function test_get_relevant_memories_respects_limit(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Memory::create([
                'student_id' => $this->student->id,
                'type' => 'mistake',
                'content' => "Memory {$i}",
                'importance' => random_int(1, 5),
            ]);
        }

        $memories = $this->service->getRelevantMemories($this->student);

        $this->assertCount(15, $memories);
    }

    public function test_get_relevant_memories_accepts_custom_limit(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Memory::create([
                'student_id' => $this->student->id,
                'type' => 'vocabulary',
                'content' => "Word {$i}",
                'importance' => $i,
            ]);
        }

        $memories = $this->service->getRelevantMemories($this->student, 5);

        $this->assertCount(5, $memories);
        $this->assertEquals(10, $memories->first()->importance);
    }

    public function test_get_relevant_memories_orders_by_created_at_desc_as_tiebreaker(): void
    {
        $first = Memory::create(['student_id' => $this->student->id, 'type' => 'mistake', 'content' => 'First', 'importance' => 3]);
        // Force the first record to be older by updating its created_at directly
        $first->forceFill(['created_at' => now()->subMinutes(5)])->saveQuietly();

        $second = Memory::create(['student_id' => $this->student->id, 'type' => 'mistake', 'content' => 'Second', 'importance' => 3]);

        $memories = $this->service->getRelevantMemories($this->student);

        $this->assertEquals($second->id, $memories->first()->id);
        $this->assertEquals($first->id, $memories->last()->id);
    }

    public function test_get_relevant_memories_only_returns_student_memories(): void
    {
        $otherStudent = Student::create([
            'name' => 'Ana Costa',
            'email' => 'ana@example.com',
            'level' => 'advanced',
            'subscription_plan' => 'free',
        ]);

        Memory::create(['student_id' => $this->student->id, 'type' => 'mistake', 'content' => 'My memory', 'importance' => 3]);
        Memory::create(['student_id' => $otherStudent->id, 'type' => 'mistake', 'content' => 'Other student memory', 'importance' => 5]);

        $memories = $this->service->getRelevantMemories($this->student);

        $this->assertCount(1, $memories);
        $this->assertEquals('My memory', $memories->first()->content);
    }
}
