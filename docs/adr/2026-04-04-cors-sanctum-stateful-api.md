# ADR: CORS + Sanctum com statefulApi() para SPA Angular

**Data**: 2026-04-04
**Status**: Aceito
**Issue**: #1 — [INFRA] Monorepo + Docker Compose

## Contexto

O Fluency AI é uma SPA Angular (porta :4200) consumindo uma API Laravel (porta :8000). Para autenticação, usamos Laravel Sanctum no modo SPA (cookies de sessão + CSRF), não tokens de API simples. Isso exige configuração específica de CORS.

## Decisão

Usar `$middleware->statefulApi()` no `bootstrap/app.php` em vez de registrar middleware manualmente.

Configurar `config/cors.php` com:
- `allowed_origins`: `[env('FRONTEND_URL')]` — nunca `['*']` em produção
- `supports_credentials: true` — obrigatório para cookies Sanctum

## Consequências

**Positivo:**
- `statefulApi()` registra automaticamente os 6 middlewares necessários para Sanctum SPA (`EncryptCookies`, `StartSession`, `VerifyCsrfToken`, `HandleCors`, etc.)
- `FRONTEND_URL` via `.env` permite configuração por ambiente (local, staging, produção) sem alterar código
- Seguro: origem explícita, não wildcard

**Negativo:**
- Requer que o Angular envie `withCredentials: true` em todas as requests autenticadas
- Requer que o Angular busque o CSRF cookie em `GET /sanctum/csrf-cookie` antes do primeiro request autenticado

## Referência

- [Laravel Sanctum SPA Authentication](https://laravel.com/docs/sanctum#spa-authentication)
- `backend/bootstrap/app.php` — implementação
- `backend/config/cors.php` — configuração
