<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetStudentProgressTool;
use App\Ai\Tools\QuizTool;
use App\Ai\Tools\SaveLearningNoteTool;
use App\Models\Student;
use Illuminate\Support\Facades\Redis;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class EnglishTeacherAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    private const HISTORY_TTL = 3600 * 24 * 7; // 7 days

    private const MAX_HISTORY = 20;

    public function __construct(
        private readonly Student $student,
        private readonly string $sessionId,
        private readonly bool $voiceMode = false,
    ) {}

    public function instructions(): Stringable|string
    {
        $name = $this->student->name;
        $level = $this->student->level;

        if ($this->voiceMode) {
            return <<<PROMPT
            You are an English teacher for {$name}, a {$level}-level Brazilian adult learner.
            You are in VOICE MODE. Rules:
            - Respond in 1-2 short spoken sentences only. No lists, no markdown, no asterisks.
            - Use natural spoken English with contractions (it's, you'll, let's).
            - Give immediate, warm feedback. Keep it conversational and encouraging.
            - If correcting, model the correct form naturally: "Great try! We'd say 'I have been' here."
            PROMPT;
        }

        return <<<PROMPT
        You are Alex, an English teacher at Fluency AI. Your student is {$name}, a {$level}-level Brazilian adult learner.

        You follow the APA pedagogical method in every interaction:

        ## ADQUIRIR (Acquire) — 30% of interactions
        When introducing a new concept:
        - Explain clearly with 1-2 relatable examples
        - Connect to Brazilian Portuguese when helpful (false friends, cognates)
        - Do NOT ask the student to produce language yet — just ensure understanding

        ## PRATICAR (Practice) — 50% of interactions
        When the student is ready to practice:
        - Guide them through exercises with graduated difficulty
        - Give immediate, specific feedback after each attempt
        - Use "Yes, and..." to build on correct answers
        - Offer hints (not answers) when the student struggles twice

        ## AJUSTAR (Adjust) — 20% of interactions
        When you detect recurring errors or plateaus:
        - Name the pattern: "I notice you often confuse X and Y"
        - Provide a targeted mini-exercise for that specific gap
        - Update your mental model of this student's weak points

        ## General rules
        - Always respond in English (unless the student is lost, then one sentence in Portuguese to re-anchor)
        - Keep responses focused and under 150 words unless explaining a complex concept
        - Be warm, encouraging, and professionally direct
        - Use `get_student_progress` before your first response to personalize the session
        PROMPT;
    }

    /**
     * @return Message[]
     */
    public function messages(): iterable
    {
        $key = $this->historyKey();
        $raw = Redis::get($key);

        if (! $raw) {
            return [];
        }

        $history = json_decode($raw, true) ?? [];

        return array_map(
            fn (array $msg) => new Message(role: $msg['role'], content: $msg['content']),
            array_slice($history, -self::MAX_HISTORY),
        );
    }

    /**
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new GetStudentProgressTool($this->student),
            new SaveLearningNoteTool($this->student),
            new QuizTool($this->student, $this->sessionId),
        ];
    }

    public function voiceChat(string $transcript): mixed
    {
        $voiceAgent = new self($this->student, $this->sessionId, voiceMode: true);

        return $voiceAgent->prompt($transcript);
    }

    public function getVoiceGreeting(): string
    {
        $name = $this->student->name;
        $streak = $this->student->streak_current;

        if ($streak > 0) {
            return "Hey {$name}! Day {$streak} — you're on a roll. What are we practicing today?";
        }

        return "Hi {$name}! Great to have you back. Ready for some English practice?";
    }

    public function appendToHistory(string $role, string $content): void
    {
        $key = $this->historyKey();
        $raw = Redis::get($key);
        $history = $raw ? json_decode($raw, true) : [];

        $history[] = ['role' => $role, 'content' => $content];

        if (count($history) > self::MAX_HISTORY * 2) {
            $history = array_slice($history, -self::MAX_HISTORY);
        }

        Redis::setex($key, self::HISTORY_TTL, json_encode($history));
    }

    private function historyKey(): string
    {
        return "fluency:chat:{$this->student->id}:{$this->sessionId}";
    }
}
