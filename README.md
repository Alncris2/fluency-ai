# Fluency AI

Plataforma SaaS de ensino de inglês com professor de IA personalizado.
Método pedagógico: **APA** (Adquirir 30% → Praticar 50% → Ajustar 20%).

---

## Pré-requisitos

- [Docker](https://docs.docker.com/get-docker/) 24+
- [Docker Compose](https://docs.docker.com/compose/) v2+
- [GNU Make](https://www.gnu.org/software/make/)
- [gh CLI](https://cli.github.com/) (opcional, para integração GitHub)

---

## Setup local

### 1. Clone o repositório

```bash
git clone https://github.com/Alncris2/fluency-ai-backend.git fluency-ai
cd fluency-ai
```

### 2. Configure as variáveis de ambiente

```bash
cp .env.example .env
```

Edite `.env` e preencha no mínimo:
- `APP_KEY` → gere com `make artisan CMD="key:generate"` após subir os containers
- `ANTHROPIC_API_KEY` → chave da API Anthropic (para o professor de IA)

### 3. Suba os containers

```bash
make up
```

Primeira vez: o Docker vai buildar as imagens (~3-5 minutos).

### 4. Configure o backend

```bash
make artisan CMD="key:generate"
make migrate
```

### 5. Acesse a aplicação

| Serviço | URL |
|---|---|
| Frontend Angular | http://localhost:4200 |
| Backend Laravel API | http://localhost:8000 |
| API Health Check | http://localhost:8000/api/health |
| pgAdmin | http://localhost:5050 |
| Mailpit (emails) | http://localhost:8025 |

---

## Comandos do dia a dia

```bash
make help          # lista todos os comandos
make up            # sobe os containers
make down          # para os containers
make logs          # logs em tempo real
make shell         # abre bash no backend
make test          # roda PHPUnit
make test-coverage # roda testes com cobertura >= 80%
make migrate       # roda migrations
make pint          # formata PHP com Pint
make artisan CMD="route:list"  # qualquer comando artisan
```

---

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 13 + laravel/ai SDK |
| Frontend | Angular 19 + Rizz template |
| Banco | PostgreSQL 16 (via Supabase em produção) |
| Cache/Queue | Redis 7 |
| Auth | Laravel Sanctum |
| IA | Claude (claude-sonnet-4-20250514) via laravel/ai |
| Streaming | SSE nativo Laravel |

---

## Estrutura do monorepo

```
fluency-ai/
├── backend/        ← Laravel 13 API
├── frontend/       ← Angular 19 + Rizz
├── docker/         ← Dockerfiles e configs nginx
│   ├── backend/
│   ├── frontend/
│   └── nginx/
├── docker-compose.yml
├── Makefile
└── .env.example
```
