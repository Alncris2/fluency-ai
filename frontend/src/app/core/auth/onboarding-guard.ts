import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthStore } from '../../store/auth/auth.store';

export const onboardingGuard: CanActivateFn = () => {
  const store = inject(AuthStore);
  const router = inject(Router);
  if (store.needsOnboarding()) return router.createUrlTree(['/onboarding/objetivo']);
  if (store.needsDiagnostic()) return router.createUrlTree(['/diagnostico/intro']);
  return true;
};
