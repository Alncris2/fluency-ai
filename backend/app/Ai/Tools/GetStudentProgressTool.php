<?php

namespace App\Ai\Tools;

use App\Models\Student;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetStudentProgressTool implements Tool
{
    public function __construct(
        private readonly Student $student,
    ) {}

    public function description(): string
    {
        return 'Returns the current student level, recent mistakes, achievements, and streak. Use at the start of each session to personalize your teaching.';
    }

    public function handle(Request $request): string
    {
        $memories = DB::table('memories')
            ->where('student_id', $this->student->id)
            ->orderByDesc('importance')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['type', 'content', 'apa_phase']);

        $mistakesSummary = $memories
            ->where('type', 'mistake')
            ->pluck('content')
            ->implode('; ') ?: 'None recorded yet';

        $achievementsSummary = $memories
            ->where('type', 'achievement')
            ->pluck('content')
            ->implode('; ') ?: 'None recorded yet';

        return json_encode([
            'name' => $this->student->name,
            'level' => $this->student->level,
            'streak' => $this->student->streak_current,
            'plan' => $this->student->subscription_plan,
            'recent_mistakes' => $mistakesSummary,
            'achievements' => $achievementsSummary,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
