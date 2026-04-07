# Fluency AI — Frontend

Angular 19 frontend da plataforma Fluency AI — professor de IA para ensino de inglês.

## Stack

- **Angular** 19.2.x (standalone components, lazy loading)
- **NgRx** 19.2.x (state management)
- **ng-bootstrap** 18.x + Bootstrap 5.3
- **Template base**: Rizz (adaptado para Fluency)

## Desenvolvimento

```bash
ng serve
# Acesse http://localhost:4200
```

## Build

```bash
ng build --configuration production
# Artefatos em dist/
```

## Testes

```bash
ng test --watch=false
```

## Arquitetura

```
src/app/
├── views/          # Features por domínio (lazy loading)
│   └── auth/       # Autenticação (login, register)
├── shared/         # Componentes reutilizáveis
├── core/           # Guards, interceptors, serviços globais
├── store/          # NgRx slices (authentication, layout)
└── layouts/        # Shells de layout (vertical/topbar)
```

## Features implementadas

| Feature | Rota | Sprint | Issue |
|---------|------|--------|-------|
| Tela de login | `/auth/log-in` | sprint_1_infra | #9 |

## Documentação

- `docs/adr/` — Architecture Decision Records
- `docs/openapi/` — Especificação OpenAPI dos endpoints

## Cores Fluency

- Primary: `#6C63FF`
- Secondary: `#1EC8A0`
