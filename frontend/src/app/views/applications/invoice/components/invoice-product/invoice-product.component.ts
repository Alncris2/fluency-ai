import { Component } from '@angular/core'
import { InvoiceData } from '../../data'
import { currency } from '@/app/common/constants'

@Component({
  selector: 'invoice-product',
  standalone: true,
  imports: [],
  templateUrl: './invoice-product.component.html',
  styles: ``,
})
export class InvoiceProductComponent {
  productList = InvoiceData
  currency = currency
}
