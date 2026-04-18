import { inject } from '@angular/core'
import { RedirectCommand, Router, type CanActivateFn, type UrlTree } from '@angular/router'
import { AuthenticationService } from '@/app/core/service/auth.service'

export const onboardingGuard: CanActivateFn = () => {
  const authService = inject(AuthenticationService)

  if (!authService.session) return true

  if (!authService.onboardingCompleted) {
    const router = inject(Router)
    const urlTree: UrlTree = router.parseUrl('/auth/onboarding')
    return new RedirectCommand(urlTree, { skipLocationChange: false })
  }

  return true
}
