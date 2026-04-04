<?php

namespace App\Ai\Tools;

use App\Models\Quiz;
use App\Models\Student;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class QuizTool implements Tool
{
    public function __construct(
        private readonly Student $student,
        private readonly string $sessionId,
    ) {}

    public function description(): string
    {
        return 'Sends an interactive quiz to the student mid-conversation to test understanding. '
            .'Use during the PRACTICE phase (Praticar) when the student has shown basic understanding of a concept. '
            .'Returns a quiz_id that the frontend uses to render an interactive card.';
    }

    public function handle(Request $request): string
    {
        $type = $request->get('type');
        $allowed = ['multiple_choice', 'fill_in_blank', 'translation', 'error_correction'];

        if (! in_array($type, $allowed, true)) {
            return json_encode([
                'error' => "Invalid quiz type '{$type}'. Allowed: ".implode(', ', $allowed),
            ]);
        }

        $quiz = Quiz::create([
            'student_id' => $this->student->id,
            'session_id' => $this->sessionId,
            'type' => $type,
            'topic' => $request->get('topic'),
            'question' => $request->get('question'),
            'options_json' => $request->get('options_json'),
            'correct_answer' => $request->get('correct_answer'),
            'explanation' => $request->get('explanation'),
            'status' => 'pending',
        ]);

        return json_encode([
            'quiz_id' => $quiz->id,
            'type' => $quiz->type,
            'topic' => $quiz->topic,
            'question' => $quiz->question,
            'options' => $quiz->options_json,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->enum(['multiple_choice', 'fill_in_blank', 'translation', 'error_correction'])
                ->description('Quiz type'),
            'topic' => $schema->string()->description('Grammar or vocabulary topic being tested (max 100 chars)'),
            'question' => $schema->string()->description('The quiz question or sentence to complete/translate'),
            'options_json' => $schema->array()->items($schema->string())
                ->description('Answer options for multiple_choice (omit for other types)')
                ->nullable(),
            'correct_answer' => $schema->string()->description('The correct answer'),
            'explanation' => $schema->string()->description('Brief explanation of why this is correct, in the student\'s language'),
        ];
    }
}
