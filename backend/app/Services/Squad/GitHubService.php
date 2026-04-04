<?php

namespace App\Services\Squad;

use Illuminate\Support\Facades\Http;

class GitHubService
{
    private string $baseUrl = 'https://api.github.com';
    private string $backendRepo = 'Alncris2/fluency-ai-backend';
    private string $frontendRepo = 'Alncris2/fluency-ai-frontend';

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('squad.github_token'),
            'Accept'        => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ];
    }

    public function getIssue(int $issueNumber, string $repo = null): ?array
    {
        $repo ??= $this->backendRepo;
        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/repos/{$repo}/issues/{$issueNumber}");

        if (! $response->ok()) return null;

        $data = $response->json();

        return [
            'number'    => $data['number'],
            'title'     => $data['title'],
            'body'      => $data['body'] ?? '',
            'labels'    => array_column($data['labels'] ?? [], 'name'),
            'milestone' => $data['milestone']['title'] ?? null,
            'state'     => $data['state'],
            'url'       => $data['html_url'],
        ];
    }

    public function listIssues(string $milestone = null, array $labels = []): array
    {
        $params = ['state' => 'open', 'per_page' => 100];
        if ($milestone) $params['milestone'] = $milestone;
        if ($labels)    $params['labels']    = implode(',', $labels);

        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/repos/{$this->backendRepo}/issues", $params);

        return $response->ok() ? $response->json() : [];
    }

    public function createPr(array $data, string $repo = null): ?array
    {
        $repo ??= $this->backendRepo;
        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/repos/{$repo}/pulls", [
                'title' => $data['title'],
                'body'  => $data['body'],
                'head'  => $data['branch'],
                'base'  => 'main',
                'draft' => true, // sempre como draft até aprovação humana
            ]);

        return $response->ok() ? $response->json() : null;
    }

    public function updateIssueLabel(int $issueNumber, string $label): void
    {
        Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/repos/{$this->backendRepo}/issues/{$issueNumber}/labels", [
                'labels' => [$label],
            ]);
    }

    public function syncIssues(array $tasks): void
    {
        // Vincula tasks do Supabase com issues do GitHub pelo título
        foreach ($tasks as $task) {
            if ($task['github_issue_id']) continue;

            $issues = $this->listIssues();
            foreach ($issues as $issue) {
                if (str_contains(strtolower($issue['title']), strtolower(substr($task['title'], 0, 30)))) {
                    \DB::table('squad_tasks')
                        ->where('id', $task['id'])
                        ->update([
                            'github_issue_id'  => $issue['number'],
                            'github_issue_url' => $issue['html_url'],
                        ]);
                    break;
                }
            }
        }
    }
}
