# Fluency AI — Monorepo

Professor de inglês com IA usando o método APA.
Leia este arquivo primeiro, depois o CLAUDE.md do projeto específico em que for trabalhar.

---

## Estrutura

```
fluency-ai/
├── CLAUDE.md                 ← este arquivo
├── backend/
│   └── CLAUDE.md             ← regras completas do backend
└── frontend/
    └── CLAUDE.md             ← regras completas do frontend
```

---

## Projeto

| Item | Detalhe |
|------|---------|
| Produto | Plataforma de ensino de inglês com professor de IA |
| Método pedagógico | APA — Adquirir (30%) → Praticar (50%) → Ajustar (20%) |
| Backend | `backend/` — Laravel 13 + `laravel/ai` SDK |
| Frontend | `frontend/` — Angular 19 + template Rizz |
| Banco | PostgreSQL Supabase (`kyhkruedsilimzrlhkdc`) + Redis |
| Auth | Laravel Sanctum — Bearer token |
| Streaming | SSE nativo Laravel |
| IA | Claude `claude-sonnet-4-20250514` via Anthropic |
| Repositórios | github.com/Alncris2/fluency-ai-backend · fluency-ai-frontend |
| GitHub Project | github.com/users/Alncris2/projects/4 (60 issues, 6 sprints) |
| Squad | Supabase — tabelas: squad_tasks, squad_agents, squad_decisions |

---

## Método APA — regra central do produto

Todo comportamento do `EnglishTeacherAgent` segue este ciclo por sessão:

```
A — ADQUIRIR  (30%)  Input rico. Agente expõe o conteúdo. Não corrija erros.
P — PRATICAR  (50%)  Aluno produz. Agente escuta. Erros anotados silenciosamente.
A — AJUSTAR   (20%)  Máximo 3 correções. Explica o porquê. Aluno repete a forma correta.
```

Qualquer código que afete o comportamento do agente deve respeitar esse ciclo.

---

## Regras globais — valem para backend e frontend

- **NUNCA** use Prism — sempre `laravel/ai` SDK no backend
- **NUNCA** instale dependências sem aprovação
- **NUNCA** crie arquivos manualmente quando há gerador equivalente (`php artisan make:*` / `ng generate`)
- Commits sempre referenciam o issue: `feat: descrição (closes #N)`
- Atualizar o status da task no Supabase antes e depois de implementar
- Máximo **4 tasks por sessão** — reportar ao humano ao atingir o limite

---

## Backend — regras críticas

> Detalhes completos em `backend/CLAUDE.md`

**Path:** `/home/friday/projects/fluency-ai/backend`

**Stack:** PHP 8.4 · Laravel 13 · `laravel/ai` · PHPUnit v12 · Pint v1

```bash
# Sempre antes de implementar
search-docs                              # busca docs versionadas (obrigatório)
database-schema                          # inspeciona tabelas antes de migrar

# Criar arquivos (nunca manualmente)
php artisan make:agent NomeAgent
php artisan make:tool NomeTool
php artisan make:controller Nome --api
php artisan make:model Nome -mf          # model + migration + factory

# Após editar qualquer PHP
vendor/bin/pint --dirty --format agent  # formatar
php artisan test --compact              # testar
```

**Proibido no backend:**
- ❌ Prism, SDK direto da Anthropic, qualquer lib de IA que não seja `laravel/ai`
- ❌ Arquivos PHP sem type hints e return types explícitos
- ❌ Controllers sem Eloquent API Resources
- ❌ Rotas sem nome (`->name(...)`)
- ❌ Commitar sem rodar Pint e testes
- ❌ Pular `search-docs` antes de implementar

---

## Frontend — regras críticas

> Detalhes completos em `frontend/CLAUDE.md`

**Path:** `/home/friday/projects/fluency-ai/frontend`

**Stack:** Angular 19 standalone · Bootstrap 5.3 SCSS · NgRx · ng-bootstrap · template Rizz

```bash
# Design system — único arquivo para mudar identidade visual
public/assets/scss/config/_variables.scss
# Cores atuais: $primary #6C63FF · $secondary #1EC8A0 · font: Be Vietnam Pro

# Criar componentes (nunca manualmente)
ng generate component views/<feature>/<component> --standalone
ng generate service core/services/<nome>

# Onde criar coisas novas
src/app/views/<feature>/          # novas features (lazy-loaded)
src/app/shared/components/        # componentes reutilizáveis
src/app/core/services/            # services
src/app/core/models/              # interfaces TypeScript
src/app/store/<feature>/          # NgRx stores por feature
```

**Apps Rizz prontos para aproveitar como base:**
- `views/apps/chat/` → base do chat com o professor
- `views/dashboards/analytics/` → base do dashboard de progresso

**Proibido no frontend:**
- ❌ Instalar libs de UI além das já presentes no Rizz
- ❌ Mexer no layout store, auth store ou estrutura base do Rizz
- ❌ Criar componentes fora de `views/`, `shared/` ou `core/`
- ❌ Hardcodar URLs — usar `environment.apiUrl`
- ❌ Commitar com erros de build/TypeScript

---

## Squad — como consultar e atualizar

```bash
# Próxima task (rodar no backend via tinker)
php artisan tinker --execute '
$r = Http::withHeaders([
    "apikey" => env("SUPABASE_ANON_KEY"),
    "Authorization" => "Bearer " . env("SUPABASE_ANON_KEY"),
])->get("https://kyhkruedsilimzrlhkdc.supabase.co/rest/v1/squad_tasks", [
    "status" => "eq.backlog",
    "order"  => "priority.asc,created_at.asc",
    "limit"  => "1", "select" => "*",
]);
$t = $r->json()[0];
echo "TASK: {$t[\"title\"]} | Issue: #{$t[\"github_issue_id\"]} | ID: {$t[\"id\"]}";
'

# Atualizar status
php artisan tinker --execute '
Http::withHeaders([
    "apikey" => env("SUPABASE_ANON_KEY"),
    "Authorization" => "Bearer " . env("SUPABASE_ANON_KEY"),
    "Content-Type" => "application/json",
    "Prefer" => "return=minimal",
])->patch(
    "https://kyhkruedsilimzrlhkdc.supabase.co/rest/v1/squad_tasks?id=eq.<UUID>",
    ["status" => "done", "completed_at" => now()->toIso8601String()]
);
echo "OK";
'
```

**Status possíveis:** `backlog` → `dev_in_progress` → `qa_testing` → `done`

---

## Fluxo autônomo por sessão

```
1. Leia este CLAUDE.md
2. Leia o CLAUDE.md do projeto em que for trabalhar (backend ou frontend)
3. Consulte o Supabase → próxima task com status=backlog
4. Marque como dev_in_progress
5. Leia o issue do GitHub (github_issue_id)
6. Implemente seguindo os critérios de aceite
7. Rode formatação + testes
8. Marque como done no Supabase
9. Commite referenciando o issue
10. Repita (máximo 4 tasks) → reporte ao humano
```

**Reporte final obrigatório:**
```
✅ SESSÃO CONCLUÍDA
Tasks: #N título → done | #N título → done
Próximas: #N título (sprint_X)
Testes: X passando / X falhando
Bloqueios: (se houver)
```