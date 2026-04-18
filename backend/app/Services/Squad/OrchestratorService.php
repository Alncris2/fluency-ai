<?php

namespace App\Services\Squad;

use Illuminate\Console\Command;

class OrchestratorService
{
    public function run(array $task, Command $command): int
    {
        $command->info("Task: {$task['title']}");
        $command->warn('OrchestratorService: implementação pendente.');

        return Command::SUCCESS;
    }
}
