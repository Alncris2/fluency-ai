import { Component } from '@angular/core'
import { NgbDropdownModule } from '@ng-bootstrap/ng-bootstrap'

@Component({
  selector: 'customer-filter',
  standalone: true,
  imports: [NgbDropdownModule],
  templateUrl: './customer-filter.component.html',
  styles: ``,
})
export class CustomerFilterComponent {}
