<?php

namespace App\Services\Squad;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\AI;
use App\Services\Squad\Agents\PmAgent;
use App\Services\Squad\Agents\DevAgent;
use App\Services\Squad\Agents\QaAgent;
use App\Services\Squad\Agents\DocsAgent;

class OrchestratorService
{
    // Fluxo de estados da squad
    const FLOW = [
        'backlog'          => 'pm',
        'pm_review'        => 'human',      // checkpoint obrigatório
        'dev_in_progress'  => 'qa',
        'qa_testing'       => 'docs',       // se passou; volta para dev se falhou
        'docs_writing'     => 'human',      // checkpoint final
    ];

    public function __construct(
        private StateService $state,
        private CheckpointService $checkpoint,
        private PmAgent $pm,
        private DevAgent $dev,
        private QaAgent $qa,
        private DocsAgent $docs,
    ) {}

    public function run(array $task, Command $console): int
    {
        $console->info("┌─ Orquestrador analisando task...");
        $console->info("│  Status atual: {$task['status']}");

        $nextAgent = $this->decideNextAgent($task);
        $console->info("│  Próximo agente: {$nextAgent}");
        $console->info('└─');
        $console->info('');

        // Checkpoint humano obrigatório
        if ($nextAgent === 'human') {
            return $this->handleHumanCheckpoint($task, $console);
        }

        // Executa o agente escolhido
        $result = match ($nextAgent) {
            'pm'   => $this->pm->execute($task, $console),
            'dev'  => $this->dev->execute($task, $console),
            'qa'   => $this->qa->execute($task, $console),
            'docs' => $this->docs->execute($task, $console),
            default => throw new \RuntimeException("Agente desconhecido: {$nextAgent}"),
        };

        // Persiste decisão
        $this->state->appendDecision($task['id'], $nextAgent, $result);

        // Verifica se QA reprovou — volta para Dev
        if ($nextAgent === 'qa' && ! $result['approved']) {
            $console->warn('⚠ QA reprovou. Retornando para Dev Agent...');
            $this->state->updateStatus($task['id'], 'dev_in_progress');
            $this->state->updateState($task['id'], 'tests', $result['results']);
            return $this->run($this->state->findTask($task['id']), $console);
        }

        // Avança status
        $newStatus = $this->advanceStatus($task['status'], $result);
        $this->state->updateStatus($task['id'], $newStatus);

        // Persiste output do agente no state
        $this->state->mergeAgentOutput($task['id'], $nextAgent, $result);

        $console->info("✅ {$nextAgent} concluído → status: {$newStatus}");
        $console->info('');

        // Continua o loop se não precisar de humano
        if ($newStatus !== 'human_review' && $newStatus !== 'done' && $newStatus !== 'blocked') {
            $updatedTask = $this->state->findTask($task['id']);
            return $this->run($updatedTask, $console);
        }

        return Command::SUCCESS;
    }

    private function decideNextAgent(array $task): string
    {
        // Task bloqueada — notifica humano
        if ($task['status'] === 'blocked') {
            return 'human';
        }

        return self::FLOW[$task['status']] ?? 'human';
    }

    private function advanceStatus(string $currentStatus, array $agentResult): string
    {
        return match ($currentStatus) {
            'backlog'         => 'pm_review',
            'pm_review'       => 'dev_in_progress',
            'dev_in_progress' => 'qa_testing',
            'qa_testing'      => $agentResult['approved'] ? 'docs_writing' : 'dev_in_progress',
            'docs_writing'    => 'human_review',
            'human_review'    => 'done',
            default           => 'blocked',
        };
    }

    private function handleHumanCheckpoint(array $task, Command $console): int
    {
        $stage = $this->resolveCheckpointStage($task['status']);

        $console->warn('');
        $console->warn('══════════════════════════════════════');
        $console->warn('  🛑 CHECKPOINT HUMANO NECESSÁRIO');
        $console->warn('══════════════════════════════════════');
        $console->warn("  Task:  {$task['title']}");
        $console->warn("  Stage: {$stage}");
        $console->warn('');

        // Mostra resumo do que foi produzido até aqui
        $this->printTaskSummary($task, $console);

        $approved = $console->confirm('Aprovar e continuar?', true);

        $feedback = null;
        if (! $approved) {
            $feedback = $console->ask('Qual o motivo da reprovação?');
        }

        $this->checkpoint->save($task['id'], $stage, $approved, $feedback);

        if ($approved) {
            // Avança para o próximo agente real
            $nextStatus = $task['status'] === 'pm_review' ? 'dev_in_progress' : 'done';
            $this->state->updateStatus($task['id'], $nextStatus);

            if ($nextStatus !== 'done') {
                $updatedTask = $this->state->findTask($task['id']);
                return $this->run($updatedTask, $console);
            }

            $console->info('✅ Task concluída e aprovada!');
        } else {
            $this->state->updateStatus($task['id'], 'blocked');
            $this->state->updateState($task['id'], 'blocked_reason', $feedback);
            $console->error("Task bloqueada: {$feedback}");
        }

        return Command::SUCCESS;
    }

    private function resolveCheckpointStage(string $status): string
    {
        return match ($status) {
            'pm_review'    => 'after_stories',
            'human_review' => 'after_docs',
            'blocked'      => 'blocked',
            default        => 'unknown',
        };
    }

    private function printTaskSummary(array $task, Command $console): void
    {
        $state = $task['state'];

        if (! empty($state['user_stories'])) {
            $console->info('  📋 User Stories:');
            foreach ($state['user_stories'] as $story) {
                $console->line("     • {$story}");
            }
        }

        if (! empty($state['code']['pr_url'])) {
            $console->info("  🔗 PR: {$state['code']['pr_url']}");
        }

        if (! empty($state['tests']['phpunit']['passed'])) {
            $p = $state['tests']['phpunit'];
            $console->info("  🧪 PHPUnit: {$p['passed']} passed / {$p['failed']} failed ({$p['coverage']})");
        }

        $console->warn('');
    }
}
