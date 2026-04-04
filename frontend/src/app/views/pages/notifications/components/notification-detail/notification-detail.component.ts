import { Component } from '@angular/core'
import { notificationDetail } from '../../data'
import { CommonModule } from '@angular/common'

@Component({
  selector: 'app-notification-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './notification-detail.component.html',
  styles: ``,
})
export class NotificationDetailComponent {
  notificationDetailData = notificationDetail
}
