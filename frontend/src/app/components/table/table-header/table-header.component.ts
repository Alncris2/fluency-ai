import { TableService } from '@/app/core/service/table.service'
import { CommonModule } from '@angular/common'
import { Component } from '@angular/core'
import { FormsModule } from '@angular/forms'

@Component({
  selector: 'app-table-header',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './table-header.component.html',
  styles: ``,
})
export class TableHeaderComponent {
  constructor(public tableService: TableService<any>) {}
}
