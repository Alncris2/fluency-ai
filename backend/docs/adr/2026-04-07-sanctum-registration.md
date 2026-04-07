# ADR: Laravel Sanctum para registro e autenticação API

**Data**: 2026-04-07
**Status**: Aceito
**Contexto**: O Fluency AI precisa de um endpoint de registro que retorne token de autenticação para o SPA Angular. O projeto já usa Sanctum para login, mas o pacote não estava instalado explicitamente como dependência.

**Decisão**: Instalado `laravel/sanctum ^4.3` como dependência explícita. O endpoint POST /api/register é público (sem middleware auth), cria o usuário e retorna um token Sanctum via `createToken()`. O trait `HasApiTokens` foi adicionado ao model User.

**Consequências**:
- **Positivo**: Autenticação stateless via tokens, consistente com o padrão existente de login
- **Positivo**: Migration `personal_access_tokens` publicada, permitindo token management
- **Negativo**: Endpoint público sem rate limiting — deve ser adicionado em sprint futuro
- **Negativo**: Erros pré-existentes em StudentPreferencesTest (401) indicam que testes de endpoints protegidos precisam usar `actingAs()` — corrigir em task separada
