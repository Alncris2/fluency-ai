<?php

namespace App\Ai\Tools;

use App\Models\Student;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class SaveLearningNoteTool implements Tool
{
    public function __construct(
        private readonly Student $student,
    ) {}

    public function description(): string
    {
        return 'Saves a learning note about the student — a recurring mistake, achievement, vocabulary gap, or breakthrough. Use when you identify a pattern worth remembering for future sessions.';
    }

    public function handle(Request $request): string
    {
        DB::table('memories')->insert([
            'student_id' => $this->student->id,
            'type' => $request->string('type'),
            'content' => mb_substr($request->string('content'), 0, 200),
            'apa_phase' => $request->string('apa_phase') ?: null,
            'importance' => max(1, min(5, (int) ($request->integer('importance') ?: 2))),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return json_encode(['saved' => true, 'type' => $request->string('type')]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()
                ->description('Type: mistake, achievement, vocabulary, preference, or breakthrough')
                ->enum(['mistake', 'achievement', 'vocabulary', 'preference', 'breakthrough'])
                ->required(),
            'content' => $schema->string()
                ->description('Description of the observation (max 200 chars)')
                ->required(),
            'apa_phase' => $schema->string()
                ->description('APA phase: acquire, practice, or adjust')
                ->enum(['acquire', 'practice', 'adjust']),
            'importance' => $schema->integer()
                ->description('Priority 1–5 (default 2)'),
        ];
    }
}
