<?php

namespace App\Services\Squad\Agents;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\AI;
use Illuminate\Support\Facades\File;
use App\Services\Squad\StateService;
use App\Services\Squad\GitHubService;

class DocsAgent
{
    public function __construct(
        private StateService $state,
        private GitHubService $github,
    ) {}

    public function execute(array $task, Command $console): array
    {
        $console->info('🟢 Docs Agent iniciando...');

        $state = is_string($task['state']) ? json_decode($task['state'], true) : $task['state'];

        $prompt = $this->buildPrompt($task, $state);

        $console->line('   Gerando documentação...');

        $response = AI::ask($prompt, [
            'system'     => $this->systemPrompt(),
            'max_tokens' => 4096,
        ]);

        $result = $this->parseResponse($response);

        // Salva docs localmente
        $this->saveDocs($result, $task, $console);

        $console->info('   ✓ OpenAPI atualizado');
        $console->info('   ✓ README section gerada');
        if ($result['adr'] ?? null) {
            $console->info('   ✓ ADR criado: ' . $result['adr']['filename']);
        }

        return [
            'docs' => [
                'openapi'        => $result['openapi_patch'] ?? null,
                'readme_section' => $result['readme_section'] ?? null,
                'adr'            => $result['adr'] ?? null,
                'wiki_url'       => null,
            ],
            'pr_description' => $result['pr_description'] ?? '',
            'action'         => 'generated_docs',
            'rationale'      => 'Docs Agent gerou OpenAPI, README e ADR para a feature',
            'output'         => $result,
        ];
    }

    private function buildPrompt(array $task, array $state): string
    {
        $stories   = implode("\n", array_map(fn($s) => "- {$s}", $state['user_stories'] ?? []));
        $backendFiles = implode(', ', $state['code']['backend_files'] ?? []);
        $testResults  = json_encode($state['tests'] ?? [], JSON_PRETTY_PRINT);

        return <<<PROMPT
        ## Feature documentar
        **Título:** {$task['title']}
        **Epic:** {$task['epic']}
        **Sprint:** {$task['sprint']}

        ## O que foi implementado
        User stories:
        {$stories}

        Arquivos backend: {$backendFiles}

        Resultados de testes:
        {$testResults}

        ## Sua tarefa
        Gere documentação completa para essa feature do Fluency AI.
        Retorne JSON com:
        - openapi_patch: "yaml parcial com novos endpoints (OpenAPI 3.1)"
        - readme_section: {"section": "nome da seção", "content": "markdown"}
        - adr: {"filename": "docs/adr/NNN-titulo.md", "content": "markdown ADR"}
        - wiki_page: {"title": "...", "content": "markdown com diagrama Mermaid"}
        - pr_description: "descrição markdown completa para o PR no GitHub"

        Documente especialmente: auth necessária, rate limits, exemplos de request/response.
        Para SSE e voice, documente como o cliente Angular deve consumir.
        Retorne APENAS o JSON.
        PROMPT;
    }

    private function systemPrompt(): string
    {
        return \DB::table('squad_agents')->where('role', 'docs')->value('system_prompt') ?? '';
    }

    private function parseResponse(string $response): array
    {
        $cleaned = preg_replace('/```json|```/', '', $response);
        $data    = json_decode(trim($cleaned), true);
        return $data ?? [];
    }

    private function saveDocs(array $result, array $task, Command $console): void
    {
        $baseDir = storage_path("squad/tasks/{$task['id']}/docs");
        File::ensureDirectoryExists($baseDir);

        if ($adr = $result['adr'] ?? null) {
            File::put("{$baseDir}/adr.md", $adr['content']);
            $console->line("   📄 {$adr['filename']}");
        }

        if ($readme = $result['readme_section'] ?? null) {
            File::put("{$baseDir}/readme-section.md", $readme['content']);
            $console->line('   📄 readme-section.md');
        }

        if ($openapi = $result['openapi_patch'] ?? null) {
            File::put("{$baseDir}/openapi-patch.yaml", $openapi);
            $console->line('   📄 openapi-patch.yaml');
        }

        if ($wiki = $result['wiki_page'] ?? null) {
            File::put("{$baseDir}/wiki.md", $wiki['content']);
            $console->line('   📄 wiki.md');
        }
    }
}
