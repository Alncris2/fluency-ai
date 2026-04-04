import { ActionReducerMap } from '@ngrx/store'
import { LayoutState, layoutReducer } from './layout/layout-reducers'
import {
  AuthenticationState,
  authenticationReducer,
} from './authentication/authentication.reducer'

export interface RootReducerState {
  layout: LayoutState
  authentication: AuthenticationState
}

export const rootReducer: ActionReducerMap<RootReducerState> = {
  layout: layoutReducer,
  authentication: authenticationReducer,
}
