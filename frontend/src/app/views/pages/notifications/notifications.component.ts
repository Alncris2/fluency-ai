import { Component } from '@angular/core'
import { AlertComponent } from './components/alert/alert.component'
import { NotificationDetailComponent } from './components/notification-detail/notification-detail.component'

@Component({
  selector: 'app-notifications',
  standalone: true,
  imports: [AlertComponent, NotificationDetailComponent],
  templateUrl: './notifications.component.html',
  styles: ``,
})
export class NotificationsComponent {}
