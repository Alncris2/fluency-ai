import { ComponentFixture, TestBed } from '@angular/core/testing'
import { ReactiveFormsModule } from '@angular/forms'
import { RouterTestingModule } from '@angular/router/testing'
import { provideMockStore } from '@ngrx/store/testing'
import { OnboardingComponent } from './onboarding.component'
import { OnboardingService } from './onboarding.service'
import { provideHttpClient } from '@angular/common/http'
import { provideHttpClientTesting } from '@angular/common/http/testing'

describe('OnboardingComponent', () => {
  let component: OnboardingComponent
  let fixture: ComponentFixture<OnboardingComponent>

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [OnboardingComponent, ReactiveFormsModule, RouterTestingModule],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        provideMockStore({ initialState: { authentication: { user: null, isLoggedIn: false, isLoading: false, error: null } } }),
        OnboardingService,
      ],
    }).compileComponents()

    fixture = TestBed.createComponent(OnboardingComponent)
    component = fixture.componentInstance
    fixture.detectChanges()
  })

  // TC1 — Smoke: componente cria sem erros
  it('should create', () => {
    expect(component).toBeTruthy()
  })

  // TC2 — currentStep inicia em 1
  it('should start on step 1', () => {
    expect(component.currentStep).toBe(1)
  })

  // TC3 — nextStep() avança para 2 com step 1 válido
  it('should advance to step 2 when nextStep() is called with valid step 1', () => {
    component.step1Form.patchValue({ preferred_name: 'João', goal: 'travel' })
    component.nextStep()
    expect(component.currentStep).toBe(2)
  })

  // TC4 — canAdvance() retorna false com Passo 1 vazio
  it('canAdvance() should return false when step 1 is empty', () => {
    component.currentStep = 1
    component.step1Form.patchValue({ preferred_name: '', goal: '' })
    expect(component.canAdvance()).toBeFalse()
  })

  // TC5 — canAdvance() retorna true quando passo 1 preenchido
  it('canAdvance() should return true when step 1 is filled', () => {
    component.currentStep = 1
    component.step1Form.patchValue({ preferred_name: 'João', goal: 'work' })
    expect(component.canAdvance()).toBeTrue()
  })

  // TC6 — prevStep() volta ao passo anterior
  it('prevStep() should go back to previous step', () => {
    component.step1Form.patchValue({ preferred_name: 'Test', goal: 'travel' })
    component.nextStep()
    expect(component.currentStep).toBe(2)
    component.prevStep()
    expect(component.currentStep).toBe(1)
  })

  // TC7 — Não volta abaixo do passo 1
  it('should not go back when on step 1', () => {
    component.prevStep()
    expect(component.currentStep).toBe(1)
  })

  // TC8 — canAdvance() falso no passo 3 sem interesses
  it('canAdvance() should return false on step 3 with no interests selected', () => {
    component.currentStep = 3
    component.step3Form.patchValue({ interests: [] })
    expect(component.canAdvance()).toBeFalse()
  })

  // TC9 — canAdvance() true no passo 3 com ao menos 1 interesse
  it('canAdvance() should return true on step 3 with at least 1 interest', () => {
    component.currentStep = 3
    component.step3Form.patchValue({ interests: ['series'] })
    expect(component.canAdvance()).toBeTrue()
  })

  // TC10 — toggleInterest() adiciona e remove interesses
  it('toggleInterest() should add and remove interests', () => {
    component.toggleInterest('music')
    expect(component.isInterestSelected('music')).toBeTrue()
    component.toggleInterest('music')
    expect(component.isInterestSelected('music')).toBeFalse()
  })

  // TC11 — canAdvance() falso no passo 4 sem dias ou horários
  it('canAdvance() should return false on step 4 with no days or times', () => {
    component.currentStep = 4
    component.step4Form.patchValue({ days: [], time_of_day: [] })
    expect(component.canAdvance()).toBeFalse()
  })

  // TC12 — totalSteps é 4
  it('should have totalSteps equal to 4', () => {
    expect(component.totalSteps).toBe(4)
  })
})
