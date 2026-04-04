import { Component } from '@angular/core'
import { pricingData, pricingIconData } from './data'
import { CommonModule } from '@angular/common'

@Component({
  selector: 'app-pricing',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './pricing.component.html',
  styles: ``,
})
export class PricingComponent {
  pricingData = pricingData
  pricingIconData = pricingIconData
}
