import { computed, inject } from '@angular/core';
import { patchState, signalStore, withComputed, withMethods, withState } from '@ngrx/signals';
import { AuthService } from '../../core/auth/auth';

export interface User {
  id: string;
  name: string;
  email: string;
  onboardingCompleted: boolean;
  diagnosticCompleted: boolean;
}

interface AuthState {
  user: User | null;
  isLoading: boolean;
  error: string | null;
}

export const AuthStore = signalStore(
  { providedIn: 'root' },
  withState<AuthState>({ user: null, isLoading: false, error: null }),
  withComputed(({ user }) => ({
    isAuthenticated: computed(() => !!user()),
    needsOnboarding: computed(() => !!user() && !user()!.onboardingCompleted),
    needsDiagnostic: computed(() => !!user() && !user()!.diagnosticCompleted),
  })),
  withMethods((store, authService = inject(AuthService)) => ({
    async login(email: string, password: string): Promise<void> {
      patchState(store, { isLoading: true, error: null });
      try {
        const user = await authService.login({ email, password });
        patchState(store, { user, isLoading: false });
      } catch {
        patchState(store, { error: 'Email ou senha incorretos.', isLoading: false });
      }
    },
    async logout(): Promise<void> {
      await authService.logout();
      patchState(store, { user: null, error: null });
    },
    async loadProfile(): Promise<void> {
      patchState(store, { isLoading: true });
      try {
        const user = await authService.me();
        patchState(store, { user, isLoading: false });
      } catch {
        patchState(store, { user: null, isLoading: false });
      }
    },
    clearError(): void {
      patchState(store, { error: null });
    },
  })),
);
