<?php

namespace App\Services\Squad\Agents;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\AI;
use App\Services\Squad\StateService;
use App\Services\Squad\GitHubService;

class PmAgent
{
    public function __construct(
        private StateService $state,
        private GitHubService $github,
    ) {}

    public function execute(array $task, Command $console): array
    {
        $console->info('🔵 PM Agent iniciando...');
        $console->line("   Analisando: {$task['title']}");

        // Busca issue do GitHub se tiver ID vinculado
        $githubContext = '';
        if ($task['github_issue_id']) {
            $console->line("   Lendo issue #" . $task['github_issue_id'] . " no GitHub...");
            $issue = $this->github->getIssue($task['github_issue_id']);
            $githubContext = $this->formatGithubIssue($issue);
        }

        // Memória global do projeto
        $projectContext = $this->state->getMemory('global', 'project_context');
        $techDecisions  = $this->state->getMemory('global', 'tech_decisions');

        $prompt = $this->buildPrompt($task, $githubContext, $projectContext, $techDecisions);

        $console->line('   Consultando Claude...');

        // Laravel AI SDK — geração com JSON estruturado
        $response = AI::ask($prompt, [
            'system'     => $this->systemPrompt(),
            'max_tokens' => 2048,
        ]);

        $result = $this->parseResponse($response);

        $console->info('   ✓ User stories geradas: ' . count($result['user_stories']));
        $console->info('   ✓ Critérios de aceite: ' . count($result['acceptance_criteria']));
        $console->info('   ✓ Complexidade: ' . $result['complexity']);

        return [
            ...$result,
            'action'    => 'generated_user_stories',
            'rationale' => 'PM analisou a task e gerou user stories com critérios de aceite mensuráveis',
            'output'    => $result,
        ];
    }

    private function buildPrompt(array $task, string $githubContext, ?array $project, ?array $decisions): string
    {
        $decisionsText = collect($decisions ?? [])
            ->map(fn($d) => "- {$d['decision']}: {$d['reason']}")
            ->join("\n");

        return <<<PROMPT
        ## Task a analisar
        **Título:** {$task['title']}
        **Sprint:** {$task['sprint']}
        **Epic:** {$task['epic']}
        **Descrição:** {$task['description']}

        {$githubContext}

        ## Decisões técnicas já tomadas
        {$decisionsText}

        ## Sua tarefa
        Analise essa task no contexto do Fluency AI e retorne um JSON com:
        - requirements: resumo claro do que precisa ser implementado
        - user_stories: array de strings no formato "Como [persona], quero [ação] para [benefício]"
        - acceptance_criteria: array de strings no formato "Given/When/Then"
        - dependencies: array de issue numbers do GitHub que são pré-requisitos
        - complexity: "S" | "M" | "L" | "XL"
        - notes: observações importantes para o Dev Agent

        Retorne APENAS o JSON, sem markdown ou explicações.
        PROMPT;
    }

    private function systemPrompt(): string
    {
        $agent = \DB::table('squad_agents')->where('role', 'pm')->value('system_prompt');
        return $agent ?? '';
    }

    private function parseResponse(string $response): array
    {
        $cleaned = preg_replace('/```json|```/', '', $response);
        $data    = json_decode(trim($cleaned), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('PM Agent retornou JSON inválido: ' . $response);
        }

        return [
            'requirements'        => $data['requirements'] ?? $data['description'] ?? '',
            'user_stories'        => $data['user_stories'] ?? [],
            'acceptance_criteria' => $data['acceptance_criteria'] ?? [],
            'dependencies'        => $data['dependencies'] ?? [],
            'complexity'          => $data['complexity'] ?? 'M',
            'notes'               => $data['notes'] ?? '',
        ];
    }

    private function formatGithubIssue(?array $issue): string
    {
        if (! $issue) return '';

        $number = $issue['number'] ?? '';
        $title = $issue['title'] ?? '';
        $labels = implode(', ', $issue['labels'] ?? []);
        $milestone = $issue['milestone'] ?? '';
        $body = $issue['body'] ?? '';

        return <<<TEXT

        ## Issue do GitHub #{$number}
        **Título:** {$title}
        **Labels:** {$labels}
        **Milestone:** {$milestone}
        **Corpo:**
        {$body}
        TEXT;
    }
}
