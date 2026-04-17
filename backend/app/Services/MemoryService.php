<?php

namespace App\Services;

use App\Ai\Agents\EnglishTeacherAgent;
use App\Models\Conversation;
use App\Models\Memory;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class MemoryService
{
    private const SESSION_TTL = 3600 * 24; // 24 hours

    private const REDIS_KEY_PATTERN = 'fluency:chat:%s:%s';

    public function buildContext(Student $student): string
    {
        $parts = [];

        $parts[] = "## Perfil\n"
            ."Nome: {$student->name}\n"
            ."Nível: {$student->level}\n"
            ."Plano: {$student->subscription_plan}";

        $plan = $student->learningPlan;
        if ($plan) {
            $weakAreas = implode(', ', $plan->weak_areas ?? []);
            $parts[] = "## Plano de Estudos\n"
                ."Unidade atual: {$plan->current_unit} | Aula: {$plan->current_lesson}\n"
                ."Sessões por semana: {$plan->sessions_per_week}\n"
                .($weakAreas ? "Áreas fracas: {$weakAreas}" : '');
        }

        $memories = $this->getRelevantMemories($student);
        if ($memories->isNotEmpty()) {
            $memoryLines = $memories->map(function (Memory $memory) {
                return "[{$memory->type}] {$memory->content}";
            })->implode("\n");

            $parts[] = "## Memórias Recentes\n{$memoryLines}";
        }

        return implode("\n\n", array_filter($parts));
    }

    public function getSessionHistory(int|string $studentId, string $sessionId): array
    {
        $key = $this->redisKey($studentId, $sessionId);
        $raw = Redis::get($key);

        if (! $raw) {
            return [];
        }

        return json_decode($raw, true) ?? [];
    }

    public function saveSessionHistory(int|string $studentId, string $sessionId, array $messages): void
    {
        $key = $this->redisKey($studentId, $sessionId);
        Redis::setex($key, self::SESSION_TTL, json_encode($messages));
    }

    public function persistConversation(Student $student, string $sessionId, array $messages): Conversation
    {
        return Conversation::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'student_id' => $student->id,
                'messages' => $messages,
                'session_type' => 'chat',
                'tokens_used' => 0,
                'last_activity_at' => now(),
            ]
        );
    }

    public function summarizeAndSave(Student $student, string $sessionId): ?Memory
    {
        $messages = $this->getSessionHistory($student->id, $sessionId);

        if (empty($messages)) {
            return null;
        }

        $transcript = collect($messages)
            ->map(fn (array $msg) => strtoupper($msg['role'] ?? 'user').': '.($msg['content'] ?? ''))
            ->implode("\n");

        $summaryPrompt = <<<PROMPT
        Analyze this English lesson session and create a brief memory note for the student's profile.

        Focus on:
        - Key mistakes or grammar gaps observed
        - Vocabulary the student learned or struggled with
        - Achievements or breakthroughs
        - Patterns to watch in future sessions

        Respond with a single concise sentence (max 100 words) starting with a memory type:
        [mistake], [achievement], [vocabulary], [preference], or [breakthrough]

        Session transcript:
        {$transcript}
        PROMPT;

        $agent = new EnglishTeacherAgent($student, $sessionId);
        $summary = (string) $agent->prompt($summaryPrompt);

        $type = 'preference';
        if (preg_match('/^\[(mistake|achievement|vocabulary|preference|breakthrough)\]/i', $summary, $matches)) {
            $type = strtolower($matches[1]);
            $summary = trim(substr($summary, strlen($matches[0])));
        }

        return Memory::create([
            'student_id' => $student->id,
            'type' => $type,
            'content' => $summary,
            'importance' => 3,
            'apa_phase' => null,
        ]);
    }

    public function getRelevantMemories(Student $student, int $limit = 15): Collection
    {
        return Memory::where('student_id', $student->id)
            ->orderByDesc('importance')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function redisKey(int|string $studentId, string $sessionId): string
    {
        return sprintf(self::REDIS_KEY_PATTERN, $studentId, $sessionId);
    }
}
