import { Routes } from '@angular/router';
import { authGuard } from './core/auth/auth-guard';
import { onboardingGuard } from './core/auth/onboarding-guard';

export const routes: Routes = [
  {
    path: '',
    redirectTo: 'dashboard',
    pathMatch: 'full',
  },
  {
    path: 'auth',
    loadComponent: () =>
      import('./layouts/auth-layout/auth-layout').then(m => m.AuthLayout),
    children: [
      { path: '', redirectTo: 'login', pathMatch: 'full' },
      {
        path: 'login',
        loadComponent: () =>
          import('./features/auth/pages/login/login').then(m => m.Login),
      },
      {
        path: 'register',
        loadComponent: () =>
          import('./features/auth/pages/register/register').then(m => m.Register),
      },
      {
        path: 'forgot-password',
        loadComponent: () =>
          import('./features/auth/pages/forgot-password/forgot-password').then(
            m => m.ForgotPassword,
          ),
      },
      {
        path: 'reset-password',
        loadComponent: () =>
          import('./features/auth/pages/reset-password/reset-password').then(
            m => m.ResetPassword,
          ),
      },
      {
        path: 'email-sent',
        loadComponent: () =>
          import('./features/auth/pages/email-sent/email-sent').then(m => m.EmailSent),
      },
    ],
  },
  {
    path: 'onboarding',
    canActivate: [authGuard],
    children: [
      { path: '', redirectTo: 'objetivo', pathMatch: 'full' },
      {
        path: 'objetivo',
        loadComponent: () =>
          import('./features/onboarding/pages/objetivo/objetivo').then(m => m.Objetivo),
      },
      {
        path: 'nivel',
        loadComponent: () =>
          import('./features/onboarding/pages/nivel/nivel').then(m => m.Nivel),
      },
      {
        path: 'interesses',
        loadComponent: () =>
          import('./features/onboarding/pages/interesses/interesses').then(
            m => m.Interesses,
          ),
      },
      {
        path: 'horario',
        loadComponent: () =>
          import('./features/onboarding/pages/horario/horario').then(m => m.Horario),
      },
    ],
  },
  {
    path: 'diagnostico',
    canActivate: [authGuard],
    children: [
      { path: '', redirectTo: 'intro', pathMatch: 'full' },
      {
        path: 'intro',
        loadComponent: () =>
          import('./features/diagnostico/pages/intro/intro').then(m => m.Intro),
      },
      {
        path: 'conversa',
        loadComponent: () =>
          import('./features/diagnostico/pages/conversa/conversa').then(m => m.Conversa),
      },
      {
        path: 'resultado',
        loadComponent: () =>
          import('./features/diagnostico/pages/resultado/resultado').then(
            m => m.Resultado,
          ),
      },
    ],
  },
  {
    path: '',
    canActivate: [authGuard, onboardingGuard],
    loadComponent: () =>
      import('./layouts/app-layout/app-layout').then(m => m.AppLayout),
    children: [
      {
        path: 'dashboard',
        loadComponent: () =>
          import('./features/dashboard/pages/dashboard/dashboard').then(
            m => m.Dashboard,
          ),
      },
      {
        path: 'chat',
        loadComponent: () =>
          import('./features/chat/pages/chat/chat').then(m => m.Chat),
      },
      {
        path: 'chat/:sessionId',
        loadComponent: () =>
          import('./features/chat/pages/chat/chat').then(m => m.Chat),
      },
      {
        path: 'estudos/plano',
        loadComponent: () =>
          import('./features/estudos/pages/study-plan/study-plan').then(
            m => m.StudyPlan,
          ),
      },
      {
        path: 'estudos/flashcards',
        loadComponent: () =>
          import('./features/estudos/pages/flashcards-home/flashcards-home').then(
            m => m.FlashcardsHome,
          ),
      },
      {
        path: 'estudos/flashcards/:deckId',
        loadComponent: () =>
          import('./features/estudos/pages/flashcard-review/flashcard-review').then(
            m => m.FlashcardReview,
          ),
      },
      {
        path: 'progresso',
        loadComponent: () =>
          import('./features/progresso/pages/progresso/progresso').then(
            m => m.Progresso,
          ),
      },
    ],
  },
  { path: '**', redirectTo: 'dashboard' },
];
