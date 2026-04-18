import { Injectable, inject } from '@angular/core'
import { HttpClient } from '@angular/common/http'
import { Observable } from 'rxjs'
import { map, tap } from 'rxjs/operators'

import { CookieService } from 'ngx-cookie-service'

export interface User {
  id?: number
  name?: string
  email?: string
  token?: string
}

export interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
  user: User | null = null

  public readonly authSessionKey = '_FLUENCY_SESSION_'
  private cookieService = inject(CookieService)

  constructor(private http: HttpClient) {}

  login(email: string, password: string): Observable<User> {
    return this.http.post<User>(`/api/login`, { email, password }).pipe(
      map((user) => {
        if (user && user.token) {
          this.user = user
          this.saveSession(user.token)
        }
        return user
      })
    )
  }

  register(payload: RegisterPayload): Observable<User> {
    return this.http.post<User>(`/api/register`, payload).pipe(
      map((user) => {
        if (user && user.token) {
          this.user = user
          this.saveSession(user.token)
        }
        return user
      })
    )
  }

  logout(): Observable<void> {
    return this.http.post<void>(`/api/logout`, {}).pipe(
      tap(() => this.clearSession())
    )
  }

  forgotPassword(email: string): Observable<{ message: string }> {
    return this.http.post<{ message: string }>(`/api/forgot-password`, { email })
  }

  resetPassword(payload: {
    token: string
    email: string
    password: string
    password_confirmation: string
  }): Observable<{ message: string }> {
    return this.http.post<{ message: string }>(`/api/reset-password`, payload)
  }

  get session(): string {
    return this.cookieService.get(this.authSessionKey)
  }

  saveSession(token: string): void {
    this.cookieService.set(this.authSessionKey, token)
    localStorage.setItem('fluency_token', token)
  }

  removeSession(): void {
    this.cookieService.delete(this.authSessionKey)
    localStorage.removeItem('fluency_token')
  }

  private clearSession(): void {
    this.removeSession()
    this.user = null
  }
}
