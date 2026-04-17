import { Component } from '@angular/core'
import { ActivityData } from '../../data'

@Component({
  selector: 'analytics-browser',
  standalone: true,
  imports: [],
  templateUrl: './browser.component.html',
  styles: ``,
})
export class BrowserComponent {
  ActivityData = ActivityData
}
