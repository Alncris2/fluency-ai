<?php

namespace App\Services\Squad;

use Illuminate\Support\Facades\DB;

class CheckpointService
{
    public function save(string $taskId, string $stage, bool $approved, ?string $feedback = null): void
    {
        DB::table('squad_checkpoints')->insert([
            'id'          => \Str::uuid(),
            'task_id'     => $taskId,
            'stage'       => $stage,
            'summary'     => $this->buildSummary($taskId, $stage),
            'approved'    => $approved,
            'feedback'    => $feedback,
            'reviewed_by' => 'human',
            'reviewed_at' => now(),
            'created_at'  => now(),
        ]);
    }

    public function hasPendingCheckpoint(string $taskId): bool
    {
        return DB::table('squad_checkpoints')
            ->where('task_id', $taskId)
            ->whereNull('approved')
            ->exists();
    }

    private function buildSummary(string $taskId, string $stage): string
    {
        $task = DB::table('squad_tasks')->where('id', $taskId)->first();
        return "Checkpoint '{$stage}' para task: {$task?->title}";
    }
}
