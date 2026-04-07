import { ComponentFixture, TestBed } from '@angular/core/testing'
import { provideHttpClient } from '@angular/common/http'
import { provideHttpClientTesting } from '@angular/common/http/testing'
import { provideRouter } from '@angular/router'

import { RegisterComponent } from './register.component'

describe('RegisterComponent', () => {
  let component: RegisterComponent
  let fixture: ComponentFixture<RegisterComponent>

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RegisterComponent],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        provideRouter([]),
      ],
    }).compileComponents()

    fixture = TestBed.createComponent(RegisterComponent)
    component = fixture.componentInstance
    fixture.detectChanges()
  })

  it('should create', () => {
    expect(component).toBeTruthy()
  })

  it('should have an invalid form when empty', () => {
    expect(component.registerForm.valid).toBeFalsy()
  })

  it('should require name with min 2 chars', () => {
    const nameControl = component.registerForm.get('name')!
    nameControl.setValue('')
    expect(nameControl.errors?.['required']).toBeTruthy()

    nameControl.setValue('A')
    expect(nameControl.errors?.['minlength']).toBeTruthy()

    nameControl.setValue('Al')
    expect(nameControl.errors).toBeNull()
  })

  it('should require a valid email', () => {
    const emailControl = component.registerForm.get('email')!
    emailControl.setValue('invalid')
    expect(emailControl.errors?.['email']).toBeTruthy()

    emailControl.setValue('test@example.com')
    expect(emailControl.errors).toBeNull()
  })

  it('should require password with min 8 chars', () => {
    const pwControl = component.registerForm.get('password')!
    pwControl.setValue('1234567')
    expect(pwControl.errors?.['minlength']).toBeTruthy()

    pwControl.setValue('12345678')
    expect(pwControl.errors).toBeNull()
  })

  it('should validate password confirmation matches', () => {
    component.registerForm.get('password')!.setValue('12345678')
    component.registerForm.get('password_confirmation')!.setValue('different')
    expect(component.registerForm.errors?.['passwordMismatch']).toBeTruthy()

    component.registerForm.get('password_confirmation')!.setValue('12345678')
    expect(component.registerForm.errors).toBeNull()
  })

  it('should be valid when all fields are correct', () => {
    component.registerForm.patchValue({
      name: 'John Doe',
      email: 'john@example.com',
      password: '12345678',
      password_confirmation: '12345678',
    })
    expect(component.registerForm.valid).toBeTruthy()
  })

  it('should not submit when form is invalid', () => {
    component.onSubmit()
    expect(component.submitted).toBeTrue()
    expect(component.isLoading).toBeFalse()
  })
})
