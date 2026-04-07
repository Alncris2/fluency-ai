import {
  login,
  loginFailure,
} from '@/app/store/authentication/authentication.actions'
import {
  getError,
  getIsLoading,
} from '@/app/store/authentication/authentication.selector'
import { AsyncPipe, NgIf } from '@angular/common'
import { Component, OnInit, inject } from '@angular/core'
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms'
import { RouterLink } from '@angular/router'
import { Store } from '@ngrx/store'
import { Observable } from 'rxjs'

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [RouterLink, ReactiveFormsModule, AsyncPipe, NgIf],
  templateUrl: './login.component.html',
  styles: ``,
})
export class LoginComponent implements OnInit {
  signInForm!: FormGroup
  submitted: boolean = false
  showPassword: boolean = false

  public fb = inject(FormBuilder)
  public store = inject(Store)

  isLoading$: Observable<boolean> = this.store.select(getIsLoading)
  error$: Observable<string | null> = this.store.select(getError)

  ngOnInit(): void {
    this.signInForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8)]],
    })
    // Clear any previous login error on init
    this.store.dispatch(loginFailure({ error: null as unknown as string }))
  }

  get formValues() {
    return this.signInForm.controls
  }

  get emailControl() {
    return this.signInForm.get('email')
  }

  get passwordControl() {
    return this.signInForm.get('password')
  }

  togglePasswordVisibility(): void {
    this.showPassword = !this.showPassword
  }

  login() {
    this.submitted = true
    if (this.signInForm.valid) {
      const email = this.formValues['email'].value
      const password = this.formValues['password'].value
      this.store.dispatch(login({ email, password }))
    }
  }
}
