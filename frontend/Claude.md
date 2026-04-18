# Fluency AI — Frontend Agent

Você é o **Dev Agent autônomo** da squad Fluency AI — responsável pelo frontend.
Leia este arquivo inteiro antes de qualquer ação.

---

## 1. Missão

Desenvolver o frontend do Fluency AI sobre o template **Rizz (Angular 19)**.
Interface do professor de inglês com IA: chat, voice, quiz, vocabulário e progresso.

Você trabalha de forma **completamente autônoma**:
1. Consulta o Supabase para a próxima task de frontend no backlog
2. Lê o issue do GitHub correspondente
3. Implementa reutilizando os componentes Rizz existentes
4. Atualiza o status no Supabase
5. Commita referenciando o issue

---

## 2. Stack — regras absolutas

| Camada | Tecnologia | Regra |
|--------|-----------|-------|
| Framework | Angular 19 standalone | Lazy loading por feature |
| CSS | Bootstrap 5.3 via SCSS | Customizado via _variables.scss |
| State | NgRx Store | Por feature — não mexa no layout store |
| UI | ng-bootstrap | Não instale outras libs de UI |
| Charts | ApexCharts + Chart.js | Já instalados |
| Ícones | Iconoir (padrão), FontAwesome | Iconoir é o padrão do sidebar |
| Font | Be Vietnam Pro | Manter sempre |
| Auth | Laravel Sanctum | Bearer token via localStorage |
| API | Laravel backend em :8000 | `http://localhost:8000/api` |

---

## 3. Supabase da squad

**Mesma lógica do backend** — consulte via HTTP antes de começar qualquer task:

```typescript
// Próxima task frontend
const resp = await fetch(
  'https://kyhkruedsilimzrlhkdc.supabase.co/rest/v1/squad_tasks' +
  '?status=eq.backlog&order=priority.asc&limit=1&select=*',
  { headers: { 'apikey': environment.supabaseAnonKey,
               'Authorization': `Bearer ${environment.supabaseAnonKey}` } }
);
const [task] = await resp.json();
console.log('TASK:', task.title, '| Issue:', task.github_issue_id);
```

---

## 4. Template Rizz — mapa completo

### Design system — arquivo único
```
public/assets/scss/config/_variables.scss
```
**Cores do Fluency AI (já aplicadas):**
- `$primary: #6C63FF` (roxo/índigo — identidade)
- `$secondary: #1EC8A0` (verde-água — acento)
- `font-family: "Be Vietnam Pro"`
- `font-size-base: 0.812rem`

### Estrutura de navegação

```
src/app/
├── common/
│   └── menu-items.ts          ← itens do sidebar (já atualizado)
├── components/
│   └── logo-box/              ← logo do Fluency AI
├── core/
│   ├── guards/                ← auth guard
│   ├── interceptors/          ← Bearer token (já implementado)
│   ├── models/                ← interfaces TypeScript
│   │   └── student.model.ts   ← já existe
│   └── services/
│       ├── auth.service.ts    ← Sanctum (já conectado)
│       └── api.service.ts     ← cliente HTTP base
├── layouts/
│   ├── layout/                ← shell principal
│   ├── vertical/              ← topbar + sidebar + content
│   ├── topbar/
│   ├── sidebar/
│   └── footer/
├── shared/
│   └── components/            ← componentes reutilizáveis
├── store/                     ← NgRx stores por feature
│   ├── layout/                ← NÃO MEXA (Rizz)
│   ├── auth/                  ← NÃO MEXA (Rizz)
│   └── <feature>/             ← crie aqui suas stores
└── views/                     ← features lazy-loaded
    ├── dashboards/
    │   └── analytics/         ← BASE para dashboard de progresso
    └── apps/
        └── chat/              ← BASE para chat com o professor
```

### Features a criar (em `src/app/views/`):

```
views/
├── aula/                      ← chat com o professor
│   ├── aula.component.ts
│   ├── aula.routes.ts
│   └── components/
│       ├── message-bubble/    ← mensagem de chat
│       ├── quiz-card/         ← card interativo de quiz
│       ├── activity-card/     ← card de atividade
│       └── video-card/        ← player YouTube embedded
├── voz/                       ← voice chat
│   ├── voz.component.ts
│   ├── voz.routes.ts
│   └── components/
│       ├── voice-button/      ← push-to-talk
│       ├── token-progress/    ← barra de tokens
│       └── transcript/        ← transcrição em tempo real
├── vocabulario/               ← dicionário pessoal
│   ├── vocabulario.component.ts
│   └── components/
│       ├── word-card/
│       └── flashcard-session/
├── progresso/                 ← dashboard de progresso
│   └── progresso.component.ts
└── onboarding/                ← diagnóstico CEFR (sem layout principal)
    └── onboarding.component.ts
```

---

## 5. Regras obrigatórias de código

### Sempre faça:
```bash
# Criar componente (nunca manualmente)
ng generate component views/<feature>/<component> --standalone

# Criar service
ng generate service core/services/<nome>

# Criar store NgRx
ng generate store store/<feature>/<feature> --module app.config.ts
```

### Estrutura de componente standalone:
```typescript
import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-<nome>',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './<nome>.component.html',
})
export class <Nome>Component { }
```

### Lazy loading obrigatório em cada feature:
```typescript
// app.routes.ts
{
  path: 'aula',
  loadComponent: () => import('./views/aula/aula.component')
    .then(m => m.AulaComponent),
  canActivate: [AuthGuard]
}
```

### API Service — padrão:
```typescript
// core/services/api.service.ts
@Injectable({ providedIn: 'root' })
export class ApiService {
  private base = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  get<T>(path: string): Observable<T> {
    return this.http.get<T>(`${this.base}/${path}`);
  }
  post<T>(path: string, body: unknown): Observable<T> {
    return this.http.post<T>(`${this.base}/${path}`, body);
  }
}
```

### SSE Streaming (chat com o professor):
```typescript
streamResponse(messageId: string): Observable<string> {
  return new Observable(observer => {
    const es = new EventSource(
      `http://localhost:8000/api/chat/stream/${messageId}`,
      { withCredentials: true }
    );
    es.onmessage = (e) => {
      const data = JSON.parse(e.data);
      if (data.done) { es.close(); observer.complete(); }
      else observer.next(data.token);
    };
    es.onerror = () => { es.close(); observer.error('SSE error'); };
    return () => es.close();
  });
}
```

### Voice Chat — MediaRecorder:
```typescript
async startRecording(): Promise<void> {
  const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
  this.recorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });
  const chunks: Blob[] = [];
  this.recorder.ondataavailable = e => chunks.push(e.data);
  this.recorder.onstop = async () => {
    const blob = new Blob(chunks, { type: 'audio/webm' });
    await this.sendAudio(blob);
  };
  this.recorder.start();
}
```

---

## 6. Componentes Rizz disponíveis — use, não recrie

```
ng-bootstrap: NgbModal, NgbToast, NgbCollapse, NgbDropdown,
              NgbProgressbar, NgbBadge, NgbPagination

Bootstrap: btn, card, alert, badge, spinner, modal, toast,
           table, form-control, form-select

Avançados (já instalados):
- SweetAlert2 → confirmações e celebrações de conquista
- Uppy → upload de arquivos (avatar do aluno)
- Dragula → drag-and-drop (se precisar)
- ApexCharts → gráficos de progresso
```

---

## 7. NgRx — stores a criar

```
store/
├── chat/
│   ├── chat.actions.ts    ← sendMessage, receiveToken, sendQuiz
│   ├── chat.reducer.ts    ← messages[], loading, currentQuiz
│   └── chat.selectors.ts
├── voice/
│   ├── voice.actions.ts   ← startSession, endSession, updateBudget
│   ├── voice.reducer.ts   ← isRecording, tokensUsed, transcript
│   └── voice.selectors.ts
└── student/
    ├── student.actions.ts ← loadProfile, updateStreak
    ├── student.reducer.ts ← profile, lessonPlan, achievements
    └── student.selectors.ts
```

---

## 8. Padrão de commit

```
feat: <descrição> (closes #N)
fix: <descrição> (fixes #N)
style: <descrição> (refs #N)
```

---

## 9. Fluxo autônomo completo

```
1. Consulte Supabase → task mais prioritária com status=backlog
2. Marque como dev_in_progress
3. Leia o issue GitHub correspondente
4. Verifique se os componentes Rizz cobrem a necessidade
5. Implemente usando ng generate (nunca crie arquivos manualmente)
6. Confirme que ng serve compila sem erros
7. Marque task como done no Supabase
8. Commita referenciando o issue
9. Próxima task (máximo 4 por sessão)
```

---

## 10. Não faça

- ❌ Instalar libs de UI além das já presentes no Rizz
- ❌ Criar arquivos Angular manualmente (use ng generate)
- ❌ Mexer no layout store, auth store ou estrutura do Rizz
- ❌ Hardcodar URLs — use `environment.apiUrl`
- ❌ Commitar com erros de build/typescript
- ❌ Criar componentes fora de `views/`, `shared/` ou `core/`
