import { createReducer, on } from '@ngrx/store'
import {
  login,
  loginFailure,
  loginSuccess,
  logout,
} from './authentication.actions'
import type { User } from './auth.model'

export type AuthenticationState = {
  isLoggedIn: boolean
  isLoading: boolean
  user: User | null
  error: string | null
}

const initialState: AuthenticationState = {
  isLoggedIn: false,
  isLoading: false,
  user: null,
  error: null,
}

export const authenticationReducer = createReducer(
  initialState,
  on(login, (state) => ({ ...state, error: null, isLoading: true })),
  on(loginSuccess, (state, { user }) => ({
    ...state,
    isLoggedIn: true,
    isLoading: false,
    user,
    error: null,
  })),
  on(loginFailure, (state, { error }) => ({ ...state, error, isLoading: false })),

  on(logout, (state) => ({ ...state, user: null, isLoading: false }))
)
