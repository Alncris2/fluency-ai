import { MenuItem } from '../core/models/menu.model'

export const MENU_ITEMS: MenuItem[] = [
  {
    key: 'aprender',
    label: 'Aprender',
    isTitle: true,
  },
  {
    key: 'aula',
    icon: 'iconoir-chat-bubble',
    label: 'Aula com o Professor',
    url: '/aula',
  },
  {
    key: 'voz',
    icon: 'iconoir-microphone',
    label: 'Praticar por Voz',
    url: '/voz',
  },
  {
    key: 'progresso',
    label: 'Progresso',
    isTitle: true,
  },
  {
    key: 'dashboard',
    icon: 'iconoir-stats-up-square',
    label: 'Dashboard',
    url: '/dashboard',
  },
  {
    key: 'vocabulario',
    icon: 'iconoir-book',
    label: 'Meu Vocabulário',
    url: '/vocabulario',
  },
  {
    key: 'conquistas',
    icon: 'iconoir-medal',
    label: 'Conquistas',
    url: '/conquistas',
  },
  {
    key: 'conta',
    label: 'Conta',
    isTitle: true,
  },
  {
    key: 'perfil',
    icon: 'iconoir-user',
    label: 'Perfil',
    url: '/perfil',
  },
  {
    key: 'planos',
    icon: 'iconoir-sparks',
    label: 'Planos',
    url: '/planos',
  },
]
