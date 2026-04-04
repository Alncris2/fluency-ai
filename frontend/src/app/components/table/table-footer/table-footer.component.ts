import { TableService } from '@/app/core/service/table.service'
import { CommonModule } from '@angular/common'
import { Component, Input } from '@angular/core'
import { NgbPaginationModule } from '@ng-bootstrap/ng-bootstrap'
import type { Observable } from 'rxjs'

@Component({
  selector: 'app-table-footer',
  standalone: true,
  imports: [CommonModule, NgbPaginationModule],
  templateUrl: './table-footer.component.html',
  styles: ``,
})
export class TableFooterComponent {
  @Input() total!: Observable<number>

  constructor(public tableService: TableService<any>) {}
}
