# ADR: Adição de `isLoading` ao AuthenticationState

**Data**: 2026-04-07
**Status**: Aceito
**Issue**: #9 — [AUTH] Tela de login Angular

## Contexto

O template Rizz possui um `AuthenticationState` no NgRx com campos `isLoggedIn`, `user` e `error`, mas sem estado de loading. O `LoginComponent` não tinha como saber se uma requisição de login estava em andamento, impossibilitando feedback visual (spinner, botão desabilitado) durante a autenticação.

## Decisão

Adicionar campo `isLoading: boolean` ao `AuthenticationState` gerenciado pelo NgRx:

- `authentication.reducer.ts`: `isLoading: true` na action `login`, `false` nas actions `loginSuccess`, `loginFailure` e `logout`
- `authentication.selector.ts`: novo selector `getIsLoading` exposto para consumo pelos componentes

## Alternativas consideradas

1. **Estado local no componente** (`isLoading` como propriedade do `LoginComponent`): descartado porque impede que outros componentes (ex: header, splash screen) reajam ao estado de autenticação em andamento.
2. **Interceptor HTTP com loading global**: descartado por excesso de complexidade para um estado de autenticação específico.

## Consequências

**Positivo:**
- Componentes de login (e futuramente register) podem exibir loading state sem duplicação
- Padrão extensível para outros flows de auth (refresh token, logout em progresso)
- Consistente com a arquitetura NgRx já adotada no projeto

**Negativo:**
- Pequena expansão do slice de authentication — requer atenção em testes de reducer
- `isLoading` não persiste entre reloads (correto — deve ser resetado ao reiniciar a sessão)
