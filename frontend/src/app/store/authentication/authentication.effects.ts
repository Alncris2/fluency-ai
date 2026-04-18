import { Inject, Injectable } from '@angular/core'
import { ActivatedRoute, Router } from '@angular/router'
import { Actions, createEffect, ofType } from '@ngrx/effects'
import { of } from 'rxjs'
import { catchError, exhaustMap, map, tap } from 'rxjs/operators'
import {
  login,
  loginFailure,
  loginSuccess,
  logout,
  logoutSuccess,
} from './authentication.actions'
import { AuthenticationService } from '@/app/core/service/auth.service'

@Injectable()
export class AuthenticationEffects {
  login$ = createEffect(() =>
    this.actions$.pipe(
      ofType(login),
      exhaustMap(({ email, password }) => {
        return this.AuthenticationService.login(email, password).pipe(
          map((user) => {
            if (user) {
              if (user.onboarding_completed === false) {
                this.router.navigate(['/auth/onboarding'])
              } else {
                const returnUrl =
                  this.route.snapshot.queryParams['returnUrl'] || '/'
                this.router.navigateByUrl(returnUrl)
              }
            }
            return loginSuccess({ user })
          }),
          catchError((error) => of(loginFailure({ error })))
        )
      })
    )
  )

  logout$ = createEffect(() =>
    this.actions$.pipe(
      ofType(logout),
      exhaustMap(() =>
        this.AuthenticationService.logout().pipe(
          tap(() => this.router.navigate(['/auth/log-in'])),
          map(() => logoutSuccess()),
          catchError(() => {
            this.router.navigate(['/auth/log-in'])
            return of(logoutSuccess())
          })
        )
      )
    )
  )

  constructor(
    @Inject(Actions) private actions$: Actions,
    private AuthenticationService: AuthenticationService,
    private router: Router,
    private route: ActivatedRoute
  ) {}
}
