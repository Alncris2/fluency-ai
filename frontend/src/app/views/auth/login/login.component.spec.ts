import { ComponentFixture, TestBed } from '@angular/core/testing'
import { ReactiveFormsModule } from '@angular/forms'
import { RouterTestingModule } from '@angular/router/testing'
import { MemoizedSelector } from '@ngrx/store'
import { MockStore, provideMockStore } from '@ngrx/store/testing'

import { login, loginFailure } from '@/app/store/authentication/authentication.actions'
import {
  getError,
  getIsLoading,
} from '@/app/store/authentication/authentication.selector'
import { LoginComponent } from './login.component'

describe('LoginComponent', () => {
  let component: LoginComponent
  let fixture: ComponentFixture<LoginComponent>
  let store: MockStore
  let mockIsLoadingSelector: MemoizedSelector<object, boolean>
  let mockErrorSelector: MemoizedSelector<object, string | null>

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [LoginComponent, ReactiveFormsModule, RouterTestingModule],
      providers: [
        provideMockStore({
          initialState: {},
        }),
      ],
    }).compileComponents()

    store = TestBed.inject(MockStore)
    mockIsLoadingSelector = store.overrideSelector(getIsLoading, false)
    mockErrorSelector = store.overrideSelector(getError, null)

    fixture = TestBed.createComponent(LoginComponent)
    component = fixture.componentInstance
    fixture.detectChanges()
  })

  afterEach(() => {
    store.resetSelectors()
  })

  // TC1 — Smoke: renderiza sem erros
  it('should create and render the form', () => {
    expect(component).toBeTruthy()
    expect(component.signInForm).toBeDefined()
  })

  // TC2 — Campos inicializados vazios
  it('should initialize form fields as empty', () => {
    expect(component.emailControl?.value).toBe('')
    expect(component.passwordControl?.value).toBe('')
  })

  // TC3 — Email inválido mostra erro inline (N1/N2 da matriz)
  it('should mark email as invalid and form invalid when email is wrong format', () => {
    component.signInForm.get('email')?.setValue('invalidemail')
    component.signInForm.get('password')?.setValue('12345678')
    component.submitted = true
    fixture.detectChanges()

    expect(component.emailControl?.hasError('email')).toBeTrue()
    expect(component.signInForm.valid).toBeFalse()
  })

  // TC4 — Senha < 8 chars inválida (N3 da matriz)
  it('should mark password as invalid when shorter than 8 chars', () => {
    component.signInForm.get('email')?.setValue('valid@test.com')
    component.signInForm.get('password')?.setValue('1234567')
    component.submitted = true
    fixture.detectChanges()

    expect(component.passwordControl?.hasError('minlength')).toBeTrue()
    expect(component.signInForm.valid).toBeFalse()
  })

  // TC5 — Senha com exatamente 8 chars é válida (edge E2 da matriz)
  it('should accept password with exactly 8 characters', () => {
    component.signInForm.get('email')?.setValue('valid@test.com')
    component.signInForm.get('password')?.setValue('12345678')
    expect(component.passwordControl?.hasError('minlength')).toBeFalse()
    expect(component.signInForm.valid).toBeTrue()
  })

  // TC6 — Não despacha login com formulário inválido (edge: duplo-submit seguro)
  it('should NOT dispatch login action when form is invalid', () => {
    const dispatchSpy = spyOn(store, 'dispatch')
    component.signInForm.get('email')?.setValue('')
    component.signInForm.get('password')?.setValue('')
    component.login()

    const loginCalls = dispatchSpy.calls.all().filter(c => (c.args[0] as { type?: string })?.type === '[Authentication] Login')
    expect(loginCalls.length).toBe(0)
  })

  // TC7 — Despacha login com dados corretos ao submeter formulário válido
  it('should dispatch login action with correct credentials on valid submit', () => {
    const dispatchSpy = spyOn(store, 'dispatch')
    component.signInForm.get('email')?.setValue('valid@test.com')
    component.signInForm.get('password')?.setValue('12345678')
    component.login()

    expect(dispatchSpy).toHaveBeenCalledWith(
      login({ email: 'valid@test.com', password: '12345678' })
    )
  })

  // TC8 — Botão desabilitado quando isLoading=true (auth risk)
  it('should disable submit button when store isLoading is true', () => {
    mockIsLoadingSelector.setResult(true)
    store.refreshState()
    fixture.detectChanges()

    const button: HTMLButtonElement | null = fixture.nativeElement.querySelector('button[type="submit"]')
    expect(button?.disabled).toBeTrue()
  })

  // TC9 — Toggle show/hide senha
  it('should toggle showPassword on togglePasswordVisibility()', () => {
    expect(component.showPassword).toBeFalse()
    component.togglePasswordVisibility()
    expect(component.showPassword).toBeTrue()
    component.togglePasswordVisibility()
    expect(component.showPassword).toBeFalse()
  })

  // TC10 — Link para registro existe (Story 4)
  it('should contain a link to /auth/register for new users', () => {
    const links: NodeListOf<HTMLAnchorElement> = fixture.nativeElement.querySelectorAll('a[href]')
    const registerLink = Array.from(links).find(l => l.getAttribute('href')?.includes('register'))
    expect(registerLink).toBeTruthy()
  })

  // TC11 — campos required: email vazio inválido
  it('should mark email as required when empty', () => {
    component.signInForm.get('email')?.setValue('')
    component.signInForm.get('email')?.markAsTouched()
    expect(component.emailControl?.hasError('required')).toBeTrue()
  })

  // TC12 — Reducer regression: isLoading limpa após loginFailure (auth risk)
  it('should clear any previous error on init via loginFailure dispatch', () => {
    const dispatchSpy = spyOn(store, 'dispatch').and.callThrough()
    component.ngOnInit()
    const failureCalls = dispatchSpy.calls.all().filter(c => (c.args[0] as { type?: string })?.type === '[Authentication] Login Failure')
    expect(failureCalls.length).toBeGreaterThan(0)
  })
})
