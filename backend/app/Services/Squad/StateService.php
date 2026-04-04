<?php

namespace App\Services\Squad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class StateService
{
    public function findTask(string $id): ?array
    {
        return DB::table('squad_tasks')
            ->where('id', $id)
            ->first()?->toArray();
    }

    public function getNextBacklogTask(): ?array
    {
        return DB::table('squad_tasks')
            ->where('status', 'backlog')
            ->orderBy('priority')
            ->orderBy('created_at')
            ->first()?->toArray();
    }

    public function getTasksBySprint(string $sprint, string $status = null): Collection
    {
        $query = DB::table('squad_tasks')
            ->where('sprint', $sprint)
            ->orderBy('priority');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get()->map(fn($t) => (array) $t);
    }

    public function updateStatus(string $taskId, string $status): void
    {
        $data = ['status' => $status, 'updated_at' => now()];

        if ($status === 'dev_in_progress' && $this->findTask($taskId)['started_at'] === null) {
            $data['started_at'] = now();
            $data['current_agent'] = 'dev';
        }

        if ($status === 'done') {
            $data['completed_at'] = now();
            $data['current_agent'] = null;
        }

        if (in_array($status, ['pm_review', 'dev_in_progress', 'qa_testing', 'docs_writing'])) {
            $data['current_agent'] = match ($status) {
                'pm_review'       => 'pm',
                'dev_in_progress' => 'dev',
                'qa_testing'      => 'qa',
                'docs_writing'    => 'docs',
            };
        }

        DB::table('squad_tasks')->where('id', $taskId)->update($data);
    }

    public function updateState(string $taskId, string $key, mixed $value): void
    {
        DB::statement(
            "UPDATE squad_tasks SET state = jsonb_set(state, ?, ?::jsonb), updated_at = now() WHERE id = ?",
            ["{{{$key}}}", json_encode($value), $taskId]
        );
    }

    public function mergeAgentOutput(string $taskId, string $agent, array $result): void
    {
        $mappings = [
            'pm'   => [
                'requirements'        => $result['requirements'] ?? null,
                'user_stories'        => $result['user_stories'] ?? [],
                'acceptance_criteria' => $result['acceptance_criteria'] ?? [],
            ],
            'dev'  => [
                'code' => $result['code'] ?? [],
            ],
            'qa'   => [
                'tests' => $result['results'] ?? [],
            ],
            'docs' => [
                'docs' => $result['docs'] ?? [],
            ],
        ];

        foreach (($mappings[$agent] ?? []) as $key => $value) {
            if ($value !== null) {
                $this->updateState($taskId, $key, $value);
            }
        }
    }

    public function appendDecision(string $taskId, string $agent, array $result): void
    {
        DB::statement(
            "SELECT append_decision(?, ?::agent_role, ?, ?, ?::jsonb)",
            [
                $taskId,
                $agent,
                $result['action'] ?? 'completed',
                $result['rationale'] ?? null,
                json_encode($result['output'] ?? null),
            ]
        );
    }

    public function getMemory(string $scope, string $key, ?string $scopeRef = null): mixed
    {
        $value = DB::table('squad_memory')
            ->where('scope', $scope)
            ->where('key', $key)
            ->when($scopeRef, fn($q) => $q->where('scope_ref', $scopeRef))
            ->value('value');

        return $value ? json_decode($value, true) : null;
    }

    public function setMemory(string $scope, string $key, mixed $value, string $agent = 'orchestrator', ?string $scopeRef = null): void
    {
        DB::table('squad_memory')->upsert(
            [
                'scope'      => $scope,
                'scope_ref'  => $scopeRef,
                'key'        => $key,
                'value'      => json_encode($value),
                'agent'      => $agent,
                'updated_at' => now(),
            ],
            ['scope', 'scope_ref', 'key'],
            ['value', 'agent', 'updated_at']
        );
    }

    public function getStats(): array
    {
        $rows = DB::select("
            SELECT
                sprint,
                COUNT(*) FILTER (WHERE status = 'backlog')          AS backlog,
                COUNT(*) FILTER (WHERE status IN ('pm_review','dev_in_progress')) AS em_progresso,
                COUNT(*) FILTER (WHERE status = 'qa_testing')       AS qa,
                COUNT(*) FILTER (WHERE status IN ('docs_writing','human_review')) AS review,
                COUNT(*) FILTER (WHERE status = 'done')             AS done,
                COUNT(*) FILTER (WHERE status = 'blocked')          AS bloqueada
            FROM squad_tasks
            GROUP BY sprint
            ORDER BY sprint
        ");

        return array_map(fn($r) => (array) $r, $rows);
    }
}
