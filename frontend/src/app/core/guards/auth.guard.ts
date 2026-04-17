import { inject } from '@angular/core'
import { RedirectCommand, Router, type CanActivateFn, type UrlTree } from '@angular/router'
import { AuthenticationService } from '@/app/core/service/auth.service'

export const authGuard: CanActivateFn = () => {
  const session = inject(AuthenticationService).session
  if (session) return true

  const router = inject(Router)
  const urlTree: UrlTree = router.parseUrl('/auth/log-in')
  return new RedirectCommand(urlTree, { skipLocationChange: true })
}
