import { Route } from '@angular/router'

export const VIEW_ROUTES: Route[] = [
  {
    path: 'dashboard',
    loadChildren: () =>
      import('./dashboards/dashboards.route').then(
        (mod) => mod.DASHBOARD_ROUTES
      ),
  },
]
