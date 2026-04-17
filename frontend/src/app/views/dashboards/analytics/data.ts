export type StateType = {
  title: string
  value: string
  icon: string
  description: {
    percentage: string
    text: string
    trend: string
  }
}

export type ActivityType = {
  type: string
  topic: string
  score: string
  duration: string
  date: string
}

type SessionType = {
  count?: number
  percentage?: number
  trend?: string
}

export type VisitType = {
  channel: string
  sessions: SessionType
  prev_period: SessionType
  change: SessionType
}

export const stateData: StateType[] = [
  {
    title: 'XP Total',
    value: '0',
    icon: 'iconoir-sparks',
    description: {
      percentage: '0%',
      text: 'XP ganho hoje',
      trend: 'positive',
    },
  },
  {
    title: 'Sequência',
    value: '0 dias',
    icon: 'iconoir-fire',
    description: {
      percentage: '0',
      text: 'Dias consecutivos',
      trend: 'positive',
    },
  },
  {
    title: 'Aulas Concluídas',
    value: '0',
    icon: 'iconoir-chat-bubble',
    description: {
      percentage: '0',
      text: 'Aulas esta semana',
      trend: 'positive',
    },
  },
]

export const ActivityData: ActivityType[] = [
  {
    type: 'Aula',
    topic: 'Presente Simples',
    score: '--',
    duration: '--',
    date: '--',
  },
  {
    type: 'Vocabulário',
    topic: 'Phrasal Verbs',
    score: '--',
    duration: '--',
    date: '--',
  },
  {
    type: 'Quiz',
    topic: 'Tempos Verbais',
    score: '--',
    duration: '--',
    date: '--',
  },
]

export const VisitsList: VisitType[] = [
  {
    channel: 'Aulas',
    sessions: { count: 0, percentage: 0 },
    prev_period: { count: 0, percentage: 0 },
    change: { percentage: 0, trend: 'up' },
  },
  {
    channel: 'Quiz',
    sessions: { count: 0, percentage: 0 },
    prev_period: { count: 0, percentage: 0 },
    change: { percentage: 0, trend: 'up' },
  },
  {
    channel: 'Vocabulário',
    sessions: { count: 0, percentage: 0 },
    prev_period: { count: 0, percentage: 0 },
    change: { percentage: 0, trend: 'up' },
  },
  {
    channel: 'Voz',
    sessions: { count: 0, percentage: 0 },
    prev_period: { count: 0, percentage: 0 },
    change: { percentage: 0, trend: 'up' },
  },
]
