<?php

namespace App\Services\Squad;

use Illuminate\Support\Collection;

class StateService
{
    public function findTask(string $taskId): ?array
    {
        return null;
    }

    public function getTasksBySprint(string $sprint, string $status): Collection
    {
        return collect();
    }

    public function getNextBacklogTask(): ?array
    {
        return null;
    }

    public function getStats(): array
    {
        return [];
    }
}
