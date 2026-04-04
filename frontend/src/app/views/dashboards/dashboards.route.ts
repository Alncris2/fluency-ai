import { Route } from '@angular/router'
import { AnalyticsComponent } from './analytics/analytics.component'
import { EcommerceComponent } from './ecommerce/ecommerce.component'

export const DASHBOARD_ROUTES: Route[] = [
  {
    path: 'analytics',
    component: AnalyticsComponent,
    data: { title: 'Analytics' },
  },
  {
    path: 'ecommerce',
    component: EcommerceComponent,
    data: { title: 'Ecommerce' },
  },
]
