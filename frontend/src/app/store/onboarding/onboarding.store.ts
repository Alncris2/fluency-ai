import { patchState, signalStore, withMethods, withState } from '@ngrx/signals';

export type OnboardingGoal = 'trabalho' | 'viagem' | 'cultura' | 'estudo' | 'outro';
export type CefrLevel = 'A1' | 'A2' | 'B1' | 'B2' | 'C1' | 'C2';

interface OnboardingState {
  currentStep: 1 | 2 | 3 | 4;
  goal: OnboardingGoal | null;
  level: CefrLevel | null;
  interests: string[];
  scheduleDays: string[];
  weeklyGoalMinutes: number;
}

export const OnboardingStore = signalStore(
  { providedIn: 'root' },
  withState<OnboardingState>({
    currentStep: 1,
    goal: null,
    level: null,
    interests: [],
    scheduleDays: [],
    weeklyGoalMinutes: 30,
  }),
  withMethods(store => ({
    setGoal(goal: OnboardingGoal): void {
      patchState(store, { goal, currentStep: 2 });
    },
    setLevel(level: CefrLevel): void {
      patchState(store, { level, currentStep: 3 });
    },
    setInterests(interests: string[]): void {
      patchState(store, { interests, currentStep: 4 });
    },
    setSchedule(days: string[], goalMinutes: number): void {
      patchState(store, { scheduleDays: days, weeklyGoalMinutes: goalMinutes });
    },
    goBack(): void {
      const current = store.currentStep();
      if (current > 1) patchState(store, { currentStep: (current - 1) as 1 | 2 | 3 | 4 });
    },
    reset(): void {
      patchState(store, {
        currentStep: 1,
        goal: null,
        level: null,
        interests: [],
        scheduleDays: [],
        weeklyGoalMinutes: 30,
      });
    },
  })),
);
