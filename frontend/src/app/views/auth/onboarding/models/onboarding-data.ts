export interface OnboardingData {
  preferred_name: string
  goal: 'travel' | 'work' | 'hobby' | ''
  english_level: 'never' | 'basic' | 'intermediate' | 'advanced' | ''
  interests: string[]
  availability: {
    days: string[]
    time_of_day: string[]
  }
}

export interface OnboardingPreferencesPayload {
  preferred_name: string
  goal: string
  english_level: string
  interests: string[]
  availability: {
    days: string[]
    time_of_day: string[]
  }
}
