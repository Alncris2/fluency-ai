import { NgIf } from '@angular/common'
import { Component, inject, OnDestroy } from '@angular/core'
import {
  AbstractControl,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  ValidationErrors,
  Validators,
} from '@angular/forms'
import { Router, RouterLink } from '@angular/router'
import { HttpErrorResponse } from '@angular/common/http'
import { Store } from '@ngrx/store'
import { AuthenticationService } from '@/app/core/service/auth.service'
import { loginSuccess } from '@/app/store/authentication/authentication.actions'
import { Subject, takeUntil } from 'rxjs'

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [RouterLink, ReactiveFormsModule, NgIf],
  templateUrl: './register.component.html',
  styles: ``,
})
export class RegisterComponent implements OnDestroy {
  registerForm!: FormGroup
  submitted = false
  isLoading = false
  showPassword = false
  showConfirmPassword = false
  backendError: string | null = null

  private fb = inject(FormBuilder)
  private authService = inject(AuthenticationService)
  private router = inject(Router)
  private store = inject(Store)
  private destroy$ = new Subject<void>()

  constructor() {
    this.registerForm = this.fb.group(
      {
        name: ['', [Validators.required, Validators.minLength(2)]],
        email: ['', [Validators.required, Validators.email]],
        password: ['', [Validators.required, Validators.minLength(8)]],
        password_confirmation: ['', [Validators.required]],
      },
      { validators: RegisterComponent.passwordMatchValidator }
    )
  }

  static passwordMatchValidator(control: AbstractControl): ValidationErrors | null {
    const password = control.get('password')?.value
    const confirmation = control.get('password_confirmation')?.value
    if (!password || !confirmation) return null
    return password === confirmation ? null : { passwordMismatch: true }
  }

  get f() {
    return this.registerForm.controls
  }

  get nameControl() {
    return this.registerForm.get('name')
  }

  get emailControl() {
    return this.registerForm.get('email')
  }

  get passwordControl() {
    return this.registerForm.get('password')
  }

  get confirmControl() {
    return this.registerForm.get('password_confirmation')
  }

  togglePasswordVisibility(): void {
    this.showPassword = !this.showPassword
  }

  toggleConfirmPasswordVisibility(): void {
    this.showConfirmPassword = !this.showConfirmPassword
  }

  onSubmit(): void {
    this.submitted = true
    this.backendError = null

    if (this.registerForm.invalid) return

    this.isLoading = true

    this.authService
      .register({
        name: this.f['name'].value,
        email: this.f['email'].value,
        password: this.f['password'].value,
        password_confirmation: this.f['password_confirmation'].value,
      })
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (user) => {
          this.store.dispatch(loginSuccess({ user }))
          this.isLoading = false
          this.router.navigateByUrl('/auth/onboarding')
        },
        error: (err: HttpErrorResponse) => {
          this.isLoading = false
          if (err.status === 422) {
            const errors = err.error?.errors
            if (errors) {
              const firstField = Object.keys(errors)[0]
              this.backendError = errors[firstField]?.[0] || 'Erro de validação.'
            } else {
              this.backendError = err.error?.message || 'Erro de validação.'
            }
          } else if (err.status === 0) {
            this.backendError = 'Erro de conexão. Tente novamente.'
          } else {
            this.backendError = 'Erro de conexão. Tente novamente.'
          }
        },
      })
  }

  ngOnDestroy(): void {
    this.destroy$.next()
    this.destroy$.complete()
  }
}
