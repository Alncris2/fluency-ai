<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Squad\OrchestratorService;
use App\Services\Squad\StateService;

class SquadRun extends Command
{
    protected $signature = 'squad:run
        {--task=     : UUID de uma task específica}
        {--sprint=   : Rodar todas as tasks de um sprint (ex: sprint_1_infra)}
        {--next      : Processar apenas a próxima task do backlog}
        {--dry-run   : Simular sem persistir nada}';

    protected $description = 'Executa a squad de agentes do Fluency AI';

    public function __construct(
        private OrchestratorService $orchestrator,
        private StateService $state,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════╗');
        $this->info('║   Fluency AI — Squad Orchestrator    ║');
        $this->info('╚══════════════════════════════════════╝');
        $this->info('');

        if ($taskId = $this->option('task')) {
            return $this->runSingleTask($taskId);
        }

        if ($sprint = $this->option('sprint')) {
            return $this->runSprint($sprint);
        }

        if ($this->option('next')) {
            return $this->runNextTask();
        }

        // Sem flag: mostra status geral
        return $this->showStatus();
    }

    private function runSingleTask(string $taskId): int
    {
        $task = $this->state->findTask($taskId);

        if (! $task) {
            $this->error("Task {$taskId} não encontrada.");
            return Command::FAILURE;
        }

        $this->info("▶ Iniciando task: {$task['title']}");
        $this->info("  Sprint: {$task['sprint']} | Status: {$task['status']}");
        $this->info('');

        return $this->orchestrator->run($task, $this);
    }

    private function runSprint(string $sprint): int
    {
        $tasks = $this->state->getTasksBySprint($sprint, 'backlog');

        if ($tasks->isEmpty()) {
            $this->warn("Nenhuma task em backlog para o sprint: {$sprint}");
            return Command::SUCCESS;
        }

        $this->info("▶ Sprint: {$sprint} — {$tasks->count()} tasks no backlog");
        $this->info('');

        foreach ($tasks as $task) {
            $this->line("  • {$task['title']}");
        }

        $this->info('');

        if (! $this->confirm('Confirma execução de todas as tasks?')) {
            return Command::SUCCESS;
        }

        foreach ($tasks as $task) {
            $result = $this->orchestrator->run($task, $this);
            if ($result === Command::FAILURE) {
                $this->error("Squad bloqueada na task: {$task['title']}");
                break;
            }
        }

        return Command::SUCCESS;
    }

    private function runNextTask(): int
    {
        $task = $this->state->getNextBacklogTask();

        if (! $task) {
            $this->info('✅ Nenhuma task pendente no backlog.');
            return Command::SUCCESS;
        }

        return $this->runSingleTask($task['id']);
    }

    private function showStatus(): int
    {
        $stats = $this->state->getStats();

        $this->table(
            ['Sprint', 'Backlog', 'Em progresso', 'QA', 'Review', 'Done', 'Bloqueada'],
            $stats
        );

        $this->info('');
        $this->info('Uso: php artisan squad:run --next');
        $this->info('     php artisan squad:run --sprint=sprint_1_infra');
        $this->info('     php artisan squad:run --task=<uuid>');

        return Command::SUCCESS;
    }
}
