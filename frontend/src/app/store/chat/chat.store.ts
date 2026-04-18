import { computed, inject } from '@angular/core';
import { patchState, signalStore, withComputed, withMethods, withState } from '@ngrx/signals';
import { ChatSse } from '../../features/chat/services/chat-sse';

export type ChatMode = 'livre' | 'role-play' | 'aula' | 'voz' | 'debrief';

export interface ChatMessage {
  id: string;
  role: 'user' | 'nina';
  content: string;
  type: 'text' | 'audio' | 'vocab' | 'quiz' | 'correction' | 'table' | 'media';
  timestamp: Date;
}

interface ChatState {
  sessionId: string | null;
  messages: ChatMessage[];
  mode: ChatMode;
  isStreaming: boolean;
  isPanelOpen: boolean;
  isVoiceActive: boolean;
  streamingContent: string;
}

export const ChatStore = signalStore(
  { providedIn: 'root' },
  withState<ChatState>({
    sessionId: null,
    messages: [],
    mode: 'livre',
    isStreaming: false,
    isPanelOpen: false,
    isVoiceActive: false,
    streamingContent: '',
  }),
  withComputed(({ messages }) => ({
    messageCount: computed(() => messages().length),
    lastMessage: computed(() => messages().at(-1) ?? null),
  })),
  withMethods((store, sseService = inject(ChatSse)) => ({
    setMode(mode: ChatMode): void {
      patchState(store, { mode });
    },
    togglePanel(): void {
      patchState(store, { isPanelOpen: !store.isPanelOpen() });
    },
    toggleVoice(): void {
      patchState(store, { isVoiceActive: !store.isVoiceActive() });
    },
    addUserMessage(content: string): void {
      const msg: ChatMessage = {
        id: crypto.randomUUID(),
        role: 'user',
        content,
        type: 'text',
        timestamp: new Date(),
      };
      patchState(store, { messages: [...store.messages(), msg] });
    },
    clearSession(): void {
      patchState(store, { messages: [], sessionId: null, streamingContent: '' });
    },
  })),
);
