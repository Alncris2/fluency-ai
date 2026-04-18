import { Injectable, inject } from '@angular/core'
import { HttpClient } from '@angular/common/http'
import { Observable } from 'rxjs'

export interface OnboardingProgress {
  currentStep: number
  step1?: Record<string, unknown>
  step2?: Record<string, unknown>
  step3?: Record<string, unknown>
  step4?: Record<string, unknown>
}

@Injectable({ providedIn: 'root' })
export class OnboardingProgressService {
  private http = inject(HttpClient)

  load(): Observable<{ progress: OnboardingProgress | null }> {
    return this.http.get<{ progress: OnboardingProgress | null }>('/api/onboarding/progress')
  }

  save(progress: OnboardingProgress): Observable<{ message: string }> {
    return this.http.put<{ message: string }>('/api/onboarding/progress', { progress })
  }

  clear(): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>('/api/onboarding/progress')
  }
}

