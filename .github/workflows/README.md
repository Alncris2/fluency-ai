# 🔄 GitHub Actions Workflows

Repositório de workflows de CI/CD para o projeto Fluency AI (Laravel 13 + Angular 19).

## 📋 Convenções Compartilhadas

### Versões de Runtime

| Runtime | Versão |
|---------|--------|
| **PHP** | 8.4 |
| **Node.js** | 22.x |
| **Composer** | v2 (latest) |
| **npm** | v11+ (latest) |

### Triggers Padrão

Todos os workflows devem ser acionados por:

```yaml
on:
  push:
    branches:
      - main
      - develop
  pull_request:
    branches:
      - main
```

**Regra**: Pull requests para `main` devem passar em TODOS os workflows. PRs para `develop` devem passar em workflows próprios.

### Cache Strategy

#### PHP (Composer)

```yaml
- uses: shivammathur/setup-php@v2
  with:
    php-version: '8.4'
    extensions: curl,mbstring,zip,bcmath
    coverage: none

- name: Cache Composer dependencies
  uses: actions/cache@v4
  with:
    path: vendor
    key: ${{ runner.os }}-composer-${{ hashFiles('backend/composer.lock') }}
    restore-keys: |
      ${{ runner.os }}-composer-
```

**Expected**: Primeira execução ~5 min, com cache ~1-2 min.

#### Node.js (npm)

```yaml
- uses: actions/setup-node@v4
  with:
    node-version: '22.x'

- name: Cache npm dependencies
  uses: actions/cache@v4
  with:
    path: ~/.npm
    key: ${{ runner.os }}-node-${{ hashFiles('frontend/package-lock.json') }}
    restore-keys: |
      ${{ runner.os }}-node-
```

**Expected**: Primeira execução ~4 min, com cache ~1-2 min.

### Artifact Paths

Workflows devem salvar artefatos em diretórios padronizados:

| Workflow | Artifact Path | Retention |
|----------|---------------|-----------|
| **backend.yml** | `test-results/backend-*.xml` | 30 days |
| **backend.yml** | `test-results/coverage/` | 30 days |
| **frontend.yml** | `test-results/frontend-*.json` | 30 days |
| **frontend.yml** | `coverage/` | 30 days |

### Environment Variables

Define-se em `env:` no job level para compartilhamento:

```yaml
env:
  APP_DEBUG: true
  APP_ENV: testing
```

### Status Badges

Adicionar ao `README.md` raiz:

```markdown
[![Backend CI](https://github.com/Alncris2/fluency-ai/actions/workflows/backend.yml/badge.svg?branch=main)](https://github.com/Alncris2/fluency-ai/actions/workflows/backend.yml)
[![Frontend CI](https://github.com/Alncris2/fluency-ai/actions/workflows/frontend.yml/badge.svg?branch=main)](https://github.com/Alncris2/fluency-ai/actions/workflows/frontend.yml)
```

---

## 📂 Workflows

### 1. backend.yml
- **Purpose**: PHP tests + linting
- **Triggers**: push (main, develop), PR (main)
- **Tools**: PHPUnit, Pint
- **Cache**: Composer
- **Duration**: ~2-3 min (with cache)

### 2. frontend.yml
- **Purpose**: Angular tests + linting
- **Triggers**: push (main, develop), PR (main)
- **Tools**: ng test, ESLint, Prettier
- **Cache**: npm
- **Duration**: ~3-4 min (with cache)

---

## 🔧 Maintenance

### Adding New Workflows

1. Create file in `/.github/workflows/name.yml`
2. Follow trigger convention (push main/develop, PR main)
3. Add cache strategy if applicable
4. Update artifact paths
5. Document in this README

### Updating Cache Keys

GitHub Actions cache can cause issues if `composer.lock` or `package-lock.json` change. Manually invalidate by:
- Appending timestamp to cache key
- Or use `actions/cache@v4` (handles most cases automatically)

---

## 📊 Performance Targets

| Metric | Target | Status |
|--------|--------|--------|
| Backend workflow duration | < 3 min | ⏳ |
| Frontend workflow duration | < 4 min | ⏳ |
| Cache hit ratio | > 70% | ⏳ |
| Artifact storage | < 500 MB | ⏳ |

---

## 🆘 Troubleshooting

### Cache not working?
- Check lock file (composer.lock, package-lock.json) is committed
- Verify cache path matches setup step
- Use `gh actions-cache delete <key>` to clear locally

### PHP extensions missing?
- Update `shivammathur/setup-php@v2` action
- Add required extension to `with.extensions` parameter

### Node modules not found in frontend?
- Ensure `npm ci` is used instead of `npm install` in CI
- Check Node.js version matches project requirements (22.x minimum)

---

**Last Updated**: 2026-04-06  
**Maintainer**: Dev Backend 🛠️
