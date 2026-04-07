import { Injectable, inject } from '@angular/core'
import { HttpClient } from '@angular/common/http'
import { Observable } from 'rxjs'
import { OnboardingPreferencesPayload } from './models/onboarding-data'

@Injectable({
  providedIn: 'root',
})
export class OnboardingService {
  private http = inject(HttpClient)

  savePreferences(
    studentId: string | number,
    payload: OnboardingPreferencesPayload
  ): Observable<unknown> {
    return this.http.patch(`/api/v1/students/${studentId}/preferences`, payload)
  }
}
