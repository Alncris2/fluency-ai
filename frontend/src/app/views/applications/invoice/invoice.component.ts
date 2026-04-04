import { Component } from '@angular/core'
import { InvoiceData } from './data'
import { AddressComponent } from './components/address/address.component'
import { InvoiceProductComponent } from './components/invoice-product/invoice-product.component'
import { InvoiceTermsComponent } from './components/invoice-terms/invoice-terms.component'
import { currentYear } from '@/app/common/constants'

@Component({
  selector: 'app-invoice',
  standalone: true,
  imports: [AddressComponent, InvoiceProductComponent, InvoiceTermsComponent],
  templateUrl: './invoice.component.html',
  styles: ``,
})
export class InvoiceComponent {
  currentYear = currentYear
}
