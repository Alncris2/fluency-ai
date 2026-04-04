import { Component } from '@angular/core'
import { OrderInfo } from '../../data'
import { currency } from '@/app/common/constants'

@Component({
  selector: 'order-details-information',
  standalone: true,
  imports: [],
  templateUrl: './information.component.html',
  styles: ``,
})
export class InformationComponent {
  orderInfo = OrderInfo
  currency = currency
}
