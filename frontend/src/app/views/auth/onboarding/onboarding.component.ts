import { Component, OnInit, inject } from '@angular/core'
import { CommonModule } from '@angular/common'
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms'
import { Router } from '@angular/router'
import { Store } from '@ngrx/store'
import { OnboardingService } from './onboarding.service'
import { OnboardingProgressService } from './onboarding-progress.service'
import { AuthenticationService } from '@/app/core/service/auth.service'
import { getUser } from '@/app/store/authentication/authentication.selector'
import { take } from 'rxjs/operators'

@Component({
  selector: 'app-onboarding',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './onboarding.component.html',
  styleUrl: './onboarding.component.scss',
})
export class OnboardingComponent implements OnInit {
  currentStep = 1
  totalSteps = 4
  isLoading = false
  errorMessage: string | null = null

  step1Form!: FormGroup
  step2Form!: FormGroup
  step3Form!: FormGroup
  step4Form!: FormGroup

  private fb = inject(FormBuilder)
  private router = inject(Router)
  private store = inject(Store)
  private onboardingService = inject(OnboardingService)
  private progressService = inject(OnboardingProgressService)
  private authService = inject(AuthenticationService)

  readonly goalOptions = [
    { value: 'travel', label: 'Viagem' },
    { value: 'work', label: 'Trabalho' },
    { value: 'hobby', label: 'Hobby' },
  ]

  readonly englishLevelOptions = [
    { value: 'never', label: 'Nunca estudei', desc: 'Estou começando do zero' },
    { value: 'basic', label: 'Básico', desc: 'Conheço palavras e frases simples' },
    { value: 'intermediate', label: 'Intermediário', desc: 'Me comunico mas tenho dificuldades' },
    { value: 'advanced', label: 'Avançado', desc: 'Tenho fluência na maioria das situações' },
  ]

  readonly interests = ['series', 'music', 'sports', 'technology', 'travel']
  readonly interestLabels: Record<string, string> = {
    series: 'Séries',
    music: 'Música',
    sports: 'Esportes',
    technology: 'Tecnologia',
    travel: 'Viagem',
  }

  readonly days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']
  readonly dayLabels: Record<string, string> = {
    mon: 'Seg',
    tue: 'Ter',
    wed: 'Qua',
    thu: 'Qui',
    fri: 'Sex',
    sat: 'Sáb',
    sun: 'Dom',
  }

  readonly timeSlots = ['morning', 'afternoon', 'evening']
  readonly timeLabels: Record<string, string> = {
    morning: 'Manhã',
    afternoon: 'Tarde',
    evening: 'Noite',
  }

  ngOnInit(): void {
    this.step1Form = this.fb.group({
      preferred_name: ['', [Validators.required, Validators.minLength(2)]],
      goal: ['', Validators.required],
    })

    this.step2Form = this.fb.group({
      english_level: ['', Validators.required],
    })

    this.step3Form = this.fb.group({
      interests: [[] as string[], [Validators.required]],
    })

    this.step4Form = this.fb.group({
      days: [[] as string[], [Validators.required]],
      time_of_day: [[] as string[], [Validators.required]],
    })

    this.restoreProgress()
  }

  get currentForm(): FormGroup {
    const forms: Record<number, FormGroup> = {
      1: this.step1Form,
      2: this.step2Form,
      3: this.step3Form,
      4: this.step4Form,
    }
    return forms[this.currentStep]
  }

  canAdvance(): boolean {
    if (this.currentStep === 3) {
      const interests: string[] = this.step3Form.get('interests')?.value ?? []
      return interests.length >= 1
    }
    if (this.currentStep === 4) {
      const days: string[] = this.step4Form.get('days')?.value ?? []
      const times: string[] = this.step4Form.get('time_of_day')?.value ?? []
      return days.length >= 1 && times.length >= 1
    }
    return this.currentForm?.valid ?? false
  }

  nextStep(): void {
    if (!this.canAdvance()) return
    if (this.currentStep < this.totalSteps) {
      this.currentStep++
      this.saveProgress()
    }
  }

  prevStep(): void {
    if (this.currentStep > 1) {
      this.currentStep--
      this.errorMessage = null
    }
  }

  isInterestSelected(interest: string): boolean {
    const interests: string[] = this.step3Form.get('interests')?.value ?? []
    return interests.includes(interest)
  }

  toggleInterest(interest: string): void {
    const control = this.step3Form.get('interests')
    const current: string[] = [...(control?.value ?? [])]
    const idx = current.indexOf(interest)
    if (idx >= 0) {
      current.splice(idx, 1)
    } else {
      current.push(interest)
    }
    control?.setValue(current)
  }

  isDaySelected(day: string): boolean {
    const days: string[] = this.step4Form.get('days')?.value ?? []
    return days.includes(day)
  }

  toggleDay(day: string): void {
    const control = this.step4Form.get('days')
    const current: string[] = [...(control?.value ?? [])]
    const idx = current.indexOf(day)
    if (idx >= 0) {
      current.splice(idx, 1)
    } else {
      current.push(day)
    }
    control?.setValue(current)
  }

  isTimeSelected(time: string): boolean {
    const times: string[] = this.step4Form.get('time_of_day')?.value ?? []
    return times.includes(time)
  }

  toggleTime(time: string): void {
    const control = this.step4Form.get('time_of_day')
    const current: string[] = [...(control?.value ?? [])]
    const idx = current.indexOf(time)
    if (idx >= 0) {
      current.splice(idx, 1)
    } else {
      current.push(time)
    }
    control?.setValue(current)
  }

  submit(): void {
    if (!this.canAdvance()) return
    this.isLoading = true
    this.errorMessage = null

    this.store
      .select(getUser)
      .pipe(take(1))
      .subscribe({
        next: (user) => {
          const studentId = user?.student_id
          if (!studentId) {
            this.errorMessage = 'Usuário não autenticado. Faça login novamente.'
            this.isLoading = false
            return
          }

          const payload = {
            preferred_name: this.step1Form.get('preferred_name')?.value,
            goal: this.step1Form.get('goal')?.value,
            english_level: this.step2Form.get('english_level')?.value,
            interests: this.step3Form.get('interests')?.value,
            availability: {
              days: this.step4Form.get('days')?.value,
              time_of_day: this.step4Form.get('time_of_day')?.value,
            },
          }

          this.onboardingService.savePreferences(studentId, payload).subscribe({
            next: () => {
              this.isLoading = false
              this.authService.markOnboardingCompleted()
              this.router.navigate(['/dashboard'])
            },
            error: (err) => {
              this.isLoading = false
              this.errorMessage =
                err?.error?.message ??
                'Ocorreu um erro ao salvar suas preferências. Tente novamente.'
            },
          })
        },
        error: () => {
          this.isLoading = false
          this.errorMessage = 'Erro ao obter dados do usuário.'
        },
      })
  }

  private restoreProgress(): void {
    this.progressService.load().pipe(take(1)).subscribe({
      next: ({ progress }) => {
        if (!progress) return
        if (progress.step1) this.step1Form.patchValue(progress.step1)
        if (progress.step2) this.step2Form.patchValue(progress.step2)
        if (progress.step3) this.step3Form.patchValue(progress.step3)
        if (progress.step4) this.step4Form.patchValue(progress.step4)
        if (progress.currentStep) this.currentStep = progress.currentStep
      },
      error: () => {},
    })
  }

  private saveProgress(): void {
    const progress = {
      currentStep: this.currentStep,
      step1: this.step1Form.value,
      step2: this.step2Form.value,
      step3: this.step3Form.value,
      step4: this.step4Form.value,
    }
    this.progressService.save(progress).pipe(take(1)).subscribe()
  }
}
