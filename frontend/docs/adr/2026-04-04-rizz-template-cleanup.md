# ADR: Limpeza do template Rizz — o que ficou e o que saiu

**Data**: 2026-04-04
**Status**: Aceito
**Issue**: #61

---

## Contexto

O frontend do Fluency AI foi bootstrapado a partir do template Rizz (Angular 19 admin template). O template inclui dezenas de módulos de demonstração (email, mapas, kanban, calendário, formulários, etc.) que não fazem parte do produto educacional.

## Decisão

### Removido

| Categoria | Itens |
|---|---|
| Views de demonstração | applications, advance_ui, forms, icons, maps, email, tables, pages, ui, charts, dashboards/ecommerce |
| Store | calendar/ (actions, reducer, effects, selectors), kanban/ (idem) |
| Components | vector-maps/WorldVectorMapComponent |
| Sub-componente de analytics | organic-traffic (dependia de vector-maps) |
| Rotas | todas exceto dashboard/analytics e auth |

**Total: 464 arquivos deletados, ~48.900 linhas.**

### Mantido

| Item | Justificativa |
|---|---|
| `views/auth/` | Login, registro, error pages — produto Fluency |
| `views/dashboards/analytics/` | Dashboard do aluno (a ser evoluído) |
| `store/authentication/` | Auth state — essencial |
| `store/layout/` | Layout state (sidebar collapse, etc.) |
| `FakeBackendProvider` | Mantido temporariamente — auth.service.ts depende de User type. **Ver decisão abaixo.** |
| `core/helpers/fake-backend.ts` | Idem |

### Decisão pendente: FakeBackendProvider

O `FakeBackendProvider` intercepta todas as chamadas HTTP e simula respostas. Ele depende de dados hardcoded no `fake-backend.ts` (User type, fake users). Para removê-lo:
1. `auth.service.ts` deve ser reescrito para chamar o backend Laravel real (`POST /api/v1/auth/login`)
2. Requer que Laravel Sanctum esteja instalado e configurado
3. **Task sugerida**: `[AUTH] Integração real de autenticação com Sanctum` (nova issue)

## Consequências

**Positivas:**
- Repositório ~85% mais limpo no frontend
- Tempo de build reduzido
- Navegação lateral já reflete o produto Fluency AI (menu atualizado anteriormente)
- Zero módulos importando dependencies de mapas ou calendário

**Negativas / Pendentes:**
- Dashboard analytics ainda usa dados de demonstração do template (a ser substituído por dados reais do aluno)
- FakeBackendProvider ainda presente até task de Sanctum
- Charts module mantido no código mas sem rota (pode ser útil no futuro para progresso)
