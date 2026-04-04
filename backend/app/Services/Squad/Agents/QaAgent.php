<?php

namespace App\Services\Squad\Agents;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\AI;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use App\Services\Squad\StateService;

class QaAgent
{
    public function __construct(private StateService $state) {}

    public function execute(array $task, Command $console): array
    {
        $console->info('🟡 QA Agent iniciando...');

        $state = is_string($task['state']) ? json_decode($task['state'], true) : $task['state'];

        // Lê arquivos gerados pelo Dev
        $codeContext = $this->readGeneratedCode($task['id']);

        $prompt = $this->buildPrompt($task, $state, $codeContext);

        $console->line('   Analisando código e gerando testes...');

        $response = AI::ask($prompt, [
            'system'     => $this->systemPrompt(),
            'max_tokens' => 6144,
        ]);

        $result = $this->parseResponse($response);

        // Salva arquivos de teste
        $this->saveTestFiles($result['test_files'] ?? [], $task['id'], $console);

        // Tenta executar testes se ambiente disponível
        $executionResults = $this->tryRunTests($console);
        if ($executionResults) {
            $result['results'] = array_merge($result['results'] ?? [], $executionResults);
        }

        $approved = empty($result['critical_bugs']) &&
                    ($result['results']['phpunit']['failed'] ?? 0) === 0;

        $console->info('   ✓ Test files gerados: ' . count($result['test_files'] ?? []));

        if ($approved) {
            $console->info('   ✅ QA aprovado!');
        } else {
            $console->error('   ❌ QA reprovado!');
            foreach ($result['critical_bugs'] ?? [] as $bug) {
                $console->error("      • {$bug}");
            }
        }

        return [
            ...$result,
            'approved'   => $approved,
            'action'     => $approved ? 'qa_approved' : 'qa_rejected',
            'rationale'  => $approved ? 'Todos os testes passaram' : 'Bugs críticos encontrados',
            'output'     => $result['results'] ?? [],
        ];
    }

    private function buildPrompt(array $task, array $state, string $codeContext): string
    {
        $criteria = implode("\n", array_map(fn($c) => "- {$c}", $state['acceptance_criteria'] ?? []));
        $notes    = $state['notes_for_qa'] ?? $state['code']['notes_for_qa'] ?? '';

        return <<<PROMPT
        ## Task para testar
        **Título:** {$task['title']}
        **Critérios de aceite:**
        {$criteria}

        ## Notas do Dev para QA
        {$notes}

        ## Código gerado
        {$codeContext}

        ## Sua tarefa
        Crie testes completos para essa implementação do Fluency AI.
        Retorne JSON com:
        - test_files: [{"path": "tests/...", "content": "..."}]
        - results: {"phpunit": {"passed":0,"failed":0,"coverage":"0%"}, "jest": {"passed":0,"failed":0,"coverage":"0%"}}
        - critical_bugs: [] (lista de bugs críticos encontrados na análise estática)
        - notes_for_dev: "o que corrigir se reprovado"

        Foque nos fluxos críticos: EnglishTeacherAgent, SSE streaming, voice chat, quiz adaptativo.
        Retorne APENAS o JSON.
        PROMPT;
    }

    private function systemPrompt(): string
    {
        return \DB::table('squad_agents')->where('role', 'qa')->value('system_prompt') ?? '';
    }

    private function parseResponse(string $response): array
    {
        $cleaned = preg_replace('/```json|```/', '', $response);
        $data    = json_decode(trim($cleaned), true);
        return $data ?? ['test_files' => [], 'results' => [], 'critical_bugs' => [], 'approved' => false];
    }

    private function readGeneratedCode(string $taskId): string
    {
        $baseDir = storage_path("squad/tasks/{$taskId}");
        if (! File::exists($baseDir)) return '(código não encontrado localmente)';

        $files  = File::allFiles($baseDir);
        $output = '';

        foreach (array_slice($files, 0, 10) as $file) {
            $relativePath = str_replace($baseDir . '/', '', $file->getPathname());
            $output .= "\n### {$relativePath}\n```\n" . File::get($file->getPathname()) . "\n```\n";
        }

        return $output ?: '(sem arquivos gerados)';
    }

    private function saveTestFiles(array $testFiles, string $taskId, Command $console): void
    {
        $baseDir = storage_path("squad/tasks/{$taskId}");
        foreach ($testFiles as $file) {
            if (empty($file['path']) || empty($file['content'])) continue;
            $fullPath = "{$baseDir}/{$file['path']}";
            File::ensureDirectoryExists(dirname($fullPath));
            File::put($fullPath, $file['content']);
            $console->line("   🧪 {$file['path']}");
        }
    }

    private function tryRunTests(Command $console): ?array
    {
        // Só executa se estiver num ambiente Laravel real com vendor
        if (! File::exists(base_path('vendor/bin/phpunit'))) {
            $console->warn('   ⚠ PHPUnit não encontrado — pulando execução real');
            return null;
        }

        $console->line('   🏃 Executando PHPUnit...');
        $result = Process::run('./vendor/bin/phpunit --coverage-text 2>&1');

        // Parse simplificado do output
        preg_match('/OK \((\d+) test/', $result->output(), $ok);
        preg_match('/FAILURES! Tests: (\d+), Assertions: \d+, Failures: (\d+)/', $result->output(), $fail);

        return [
            'phpunit' => [
                'passed'   => (int) ($ok[1] ?? ($fail[1] ?? 0)),
                'failed'   => (int) ($fail[2] ?? 0),
                'coverage' => null,
            ],
        ];
    }
}
