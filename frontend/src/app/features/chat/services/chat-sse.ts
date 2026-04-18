import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ChatSse {
  /** Abre um EventSource para o endpoint SSE e emite cada token recebido. */
  stream(sessionId: string): Observable<string> {
    return new Observable(observer => {
      const es = new EventSource(`/api/chat/${sessionId}/stream`);

      es.onmessage = e => observer.next(e.data as string);

      // Evento "done" sinaliza fim do streaming — SEMPRE fechar o EventSource
      es.addEventListener('done', () => {
        es.close();
        observer.complete();
      });

      es.onerror = () => {
        es.close();
        observer.error(new Error('SSE connection error'));
      };

      // Teardown: garante fechamento ao unsubscribe
      return () => es.close();
    });
  }
}
