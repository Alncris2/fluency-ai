<?php
// config/squad.php
return [

    /*
    |--------------------------------------------------------------------------
    | GitHub
    |--------------------------------------------------------------------------
    | Token gerado em github.com/settings/tokens com permissão: repo
    | NUNCA commite o token. Use a variável de ambiente SQUAD_GITHUB_TOKEN.
    */
    'github_token' => env('SQUAD_GITHUB_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Repositórios
    |--------------------------------------------------------------------------
    */
    'repos' => [
        'backend'  => env('SQUAD_REPO_BACKEND',  'Alncris2/fluency-ai-backend'),
        'frontend' => env('SQUAD_REPO_FRONTEND', 'Alncris2/fluency-ai-frontend'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supabase / PostgreSQL
    |--------------------------------------------------------------------------
    | A squad usa a conexão 'pgsql' padrão do Laravel.
    | Configure no .env: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
    */

    /*
    |--------------------------------------------------------------------------
    | AI — Laravel AI SDK
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'model'      => env('SQUAD_AI_MODEL', 'claude-sonnet-4-20250514'),
        'max_tokens' => env('SQUAD_AI_MAX_TOKENS', 8192),
        'provider'   => env('SQUAD_AI_PROVIDER', 'anthropic'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Limites por agente
    |--------------------------------------------------------------------------
    */
    'agents' => [
        'pm'   => ['max_tokens' => 2048],
        'dev'  => ['max_tokens' => 8192],
        'qa'   => ['max_tokens' => 6144],
        'docs' => ['max_tokens' => 4096],
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkpoints
    |--------------------------------------------------------------------------
    | Define em quais transições de status o humano é consultado
    */
    'checkpoints' => [
        'after_stories' => true,  // após PM gerar user stories
        'before_commit' => true,  // antes de Dev commitar
        'after_tests'   => false, // QA pode avançar automaticamente se passou
        'before_sprint' => true,  // confirmação manual antes de nova sprint
    ],

];
