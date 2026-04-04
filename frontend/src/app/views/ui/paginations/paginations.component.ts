import { Component } from '@angular/core'
import { NgbPaginationModule } from '@ng-bootstrap/ng-bootstrap'

@Component({
  selector: 'app-paginations',
  standalone: true,
  imports: [NgbPaginationModule],
  templateUrl: './paginations.component.html',
  styles: ``,
})
export class PaginationsComponent {}
