<?php

namespace App\Services\Squad\Agents;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\AI;
use Illuminate\Support\Facades\File;
use App\Services\Squad\StateService;
use App\Services\Squad\GitHubService;

class DevAgent
{
    public function __construct(
        private StateService $state,
        private GitHubService $github,
    ) {}

    public function execute(array $task, Command $console): array
    {
        $console->info('🔵 Dev Agent iniciando...');
        $console->line("   Task: {$task['title']}");

        $state = is_string($task['state']) ? json_decode($task['state'], true) : $task['state'];

        $prompt = $this->buildPrompt($task, $state);

        $console->line('   Gerando código com Claude...');

        $response = AI::ask($prompt, [
            'system'     => $this->systemPrompt(),
            'max_tokens' => 8192,
        ]);

        $result = $this->parseResponse($response);

        // Salva arquivos localmente (para review humano antes de commitar)
        $savedFiles = $this->saveFiles($result, $task, $console);

        $console->info('   ✓ Backend files: ' . count($result['backend_files']));
        $console->info('   ✓ Frontend files: ' . count($result['frontend_files']));
        $console->info('   ✓ Migrations: ' . count($result['migrations']));

        if (! empty($result['artisan_commands'])) {
            $console->info('   📋 Artisan commands sugeridos:');
            foreach ($result['artisan_commands'] as $cmd) {
                $console->line("      $ {$cmd}");
            }
        }

        return [
            'code' => [
                'backend_files'  => array_column($result['backend_files'], 'path'),
                'frontend_files' => array_column($result['frontend_files'], 'path'),
                'migrations'     => array_column($result['migrations'], 'name'),
                'pr_url'         => null, // preenchido após checkpoint humano + commit
                'branch'         => "squad/task-" . substr($task['id'], 0, 8),
            ],
            'files_saved'     => $savedFiles,
            'pr_description'  => $result['pr_description'] ?? '',
            'notes_for_qa'    => $result['notes_for_qa'] ?? '',
            'action'          => 'generated_code',
            'rationale'       => 'Dev Agent gerou implementação completa para backend e frontend',
            'output'          => ['files_count' => count($savedFiles)],
        ];
    }

    private function buildPrompt(array $task, array $state): string
    {
        $stories  = implode("\n", array_map(fn($s) => "- {$s}", $state['user_stories'] ?? []));
        $criteria = implode("\n", array_map(fn($c) => "- {$c}", $state['acceptance_criteria'] ?? []));
        $notes    = $state['notes'] ?? '';

        // Inclui decisões anteriores para evitar retrabalho
        $decisions = collect($task['decisions'] ?? [])
            ->map(fn($d) => "- [{$d['agent']}] {$d['action']}")
            ->join("\n");

        return <<<PROMPT
        ## Task de desenvolvimento
        **Título:** {$task['title']}
        **Sprint:** {$task['sprint']}
        **Requisitos:** {$state['requirements']}

        ## User Stories
        {$stories}

        ## Critérios de aceite
        {$criteria}

        ## Notas do PM
        {$notes}

        ## Decisões anteriores da squad
        {$decisions}

        ## Sua tarefa
        Implemente essa feature completa para o Fluency AI.
        Retorne um JSON com:
        - backend_files: [{"path": "app/...", "content": "<?php ..."}]
        - frontend_files: [{"path": "src/app/...", "content": "..."}]
        - migrations: [{"name": "2025_xx_xx_create_...", "content": "<?php ..."}]
        - artisan_commands: ["php artisan ..."]
        - pr_description: "texto markdown para o PR"
        - notes_for_qa: "pontos críticos para testar"

        Siga RIGOROSAMENTE os padrões do projeto (Laravel AI SDK, não Prism; Angular standalone).
        Retorne APENAS o JSON.
        PROMPT;
    }

    private function systemPrompt(): string
    {
        return \DB::table('squad_agents')->where('role', 'dev')->value('system_prompt') ?? '';
    }

    private function parseResponse(string $response): array
    {
        $cleaned = preg_replace('/```json|```/', '', $response);
        $data    = json_decode(trim($cleaned), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Dev Agent retornou JSON inválido');
        }

        return [
            'backend_files'  => $data['backend_files'] ?? [],
            'frontend_files' => $data['frontend_files'] ?? [],
            'migrations'     => $data['migrations'] ?? [],
            'artisan_commands' => $data['artisan_commands'] ?? [],
            'pr_description' => $data['pr_description'] ?? '',
            'notes_for_qa'   => $data['notes_for_qa'] ?? '',
        ];
    }

    private function saveFiles(array $result, array $task, Command $console): array
    {
        $saved   = [];
        $baseDir = storage_path("squad/tasks/{$task['id']}");

        $allFiles = [
            ...$result['backend_files'],
            ...$result['frontend_files'],
            ...$result['migrations'],
        ];

        foreach ($allFiles as $file) {
            if (empty($file['path']) || empty($file['content'])) continue;

            $fullPath = "{$baseDir}/{$file['path']}";
            File::ensureDirectoryExists(dirname($fullPath));
            File::put($fullPath, $file['content']);

            $saved[] = $file['path'];
            $console->line("   💾 {$file['path']}");
        }

        return $saved;
    }
}
