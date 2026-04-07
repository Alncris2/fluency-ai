# Fluency AI — Backend

Laravel 13 + laravel/ai SDK — API do professor de IA para ensino de inglês.

## Stack

- **Laravel** 13 + **laravel/ai** `^0.4.3`
- **PostgreSQL** via Supabase
- **Redis** (cache, filas, sessões)
- **Auth**: Laravel Sanctum
- **IA**: `claude-sonnet-4-20250514` via laravel/ai SDK
- **Streaming**: SSE nativo (`StreamedResponse`)

## Desenvolvimento

```bash
php artisan serve
# API em http://localhost:8000
```

## Testes

```bash
php artisan test
php artisan test --filter=NomeDoTest
```

## Formatação

```bash
vendor/bin/pint --dirty
```

## Arquitetura

```
app/
├── Ai/Agents/          # Agentes de IA (EnglishTeacherAgent, ...)
├── Http/
│   ├── Controllers/Api/ # Controllers finos
│   ├── Requests/        # FormRequests com validação
│   └── Resources/       # API Resources
├── Models/              # Eloquent models (Student, User, ...)
└── Services/            # Regras de negócio
```

## Endpoints implementados

| Method | Path | Auth | Descrição |
|--------|------|------|-----------|
| GET | /api/health | — | Health check |
| POST | /api/v1/students/{student}/chat | — | Chat com EnglishTeacherAgent |
| POST | /api/v1/students/{student}/chat/stream | — | Chat SSE streaming |
| POST | /api/v1/students/{student}/chat/voice | — | Chat por voz |
| GET | /api/v1/students/{student}/chat/voice/greeting | — | Saudação de voz |
| POST | /api/v1/quiz/{quiz}/answer | — | Responder quiz |
| PATCH | /api/v1/students/{student}/preferences | sanctum | Salvar preferências do onboarding |

## Documentação

- `docs/openapi/` — Especificação OpenAPI 3.1 por recurso
- `docs/adr/` — Architecture Decision Records
