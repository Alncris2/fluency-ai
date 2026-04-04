import { currentYear } from '@/app/common/constants'
import { Component } from '@angular/core'

@Component({
  selector: 'app-cards',
  standalone: true,
  imports: [],
  templateUrl: './cards.component.html',
  styles: ``,
})
export class CardsComponent {
  currentYear = currentYear
}
