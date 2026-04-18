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
  student_id?: string
  onboarding_completed?: boolean
}

export interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

interface AuthResponse {
  user: { id: number; name: string; email: string }
  token: string
  student_id?: string
  onboarding_completed?: boolean
}

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
  user: User | null = null

  public readonly authSessionKey = '_FLUENCY_SESSION_'
  private cookieService = inject(CookieService)

  constructor(private http: HttpClient) {}

  login(email: string, password: string): Observable<User> {
    return this.http.post<AuthResponse>(`/api/login`, { email, password }).pipe(
      map((response) => {
        const user: User = {
          ...response.user,
          token: response.token,
          student_id: response.student_id,
          onboarding_completed: response.onboarding_completed,
        }
        this.user = user
        this.saveSession(response.token)
        if (response.student_id) this.saveStudentId(response.student_id)
        this.saveOnboardingStatus(response.onboarding_completed ?? false)
        return user
      })
    )
  }

  register(payload: RegisterPayload): Observable<User> {
    return this.http.post<AuthResponse>(`/api/register`, payload).pipe(
      map((response) => {
        const user: User = {
          ...response.user,
          token: response.token,
          student_id: response.student_id,
          onboarding_completed: false,
        }
        this.user = user
        this.saveSession(response.token)
        if (response.student_id) this.saveStudentId(response.student_id)
        this.saveOnboardingStatus(false)
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
    localStorage.removeItem('fluency_student_id')
    localStorage.removeItem('fluency_onboarding_completed')
  }

  get studentId(): string | null {
    return localStorage.getItem('fluency_student_id')
  }

  get onboardingCompleted(): boolean {
    return localStorage.getItem('fluency_onboarding_completed') === 'true'
  }

  markOnboardingCompleted(): void {
    this.saveOnboardingStatus(true)
  }

  private saveStudentId(id: string): void {
    localStorage.setItem('fluency_student_id', id)
  }

  private saveOnboardingStatus(completed: boolean): void {
    localStorage.setItem('fluency_onboarding_completed', String(completed))
  }

  private clearSession(): void {
    this.removeSession()
    this.user = null
  }
}
