# ADR: QuizTool — estratégia de persistência e resposta

**Data**: 2026-04-04
**Status**: Aceito
**Issue**: #20

---

## Contexto

O EnglishTeacherAgent precisa enviar quizzes interativos durante a conversa (fase Praticar do APA). O frontend deve renderizar um card interativo ao receber o quiz_id.

## Decisão

1. **QuizTool persiste em banco** — em vez de retornar apenas o quiz inline na conversa, a tool cria um registro na tabela `quizzes` e retorna o `quiz_id`. O frontend usa esse ID para renderizar um card interativo, separando apresentação de lógica.

2. **Endpoint separado para resposta** — `POST /api/v1/quiz/{id}/answer` em vez de processar a resposta dentro do agent. Isso permite: (a) o frontend submeter a resposta de forma assíncrona; (b) registrar `student_answer`, `score` e `answered_at` sem depender do histórico da conversa.

3. **Status enum `pending/answered`** — idempotência na resposta: uma tentativa de responder quiz já respondido retorna 422, evitando score duplicado.

4. **Score normalizado 0.0–1.0** — permite futura extensão para avaliação parcial (fill_in_blank com typos, por exemplo) sem quebrar o schema.

## Consequências

**Positivas:**
- Frontend desacoplado do agent para renderizar quizzes
- Histórico de quizzes queryável por student_id/session_id
- Seguro contra resposta dupla

**Negativas:**
- Requer migration e model separados (maior superfície de código)
- E2E depende de integração frontend ainda não implementada
