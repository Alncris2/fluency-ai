import { TableFooterComponent } from '@/app/components/table/table-footer/table-footer.component'
import { TableHeaderComponent } from '@/app/components/table/table-header/table-header.component'
import {
  NgbdSortableHeader,
  type SortEvent,
} from '@/app/core/directive/sortable.directive'
import { TableService } from '@/app/core/service/table.service'
import { AsyncPipe, CommonModule } from '@angular/common'
import { Component, ViewChildren, type QueryList } from '@angular/core'
import type { Observable } from 'rxjs'
import { UserData, type UserType } from './data'

@Component({
  selector: 'app-users',
  standalone: true,
  imports: [
    AsyncPipe,
    TableHeaderComponent,
    TableFooterComponent,
    NgbdSortableHeader,
    CommonModule,
  ],
  templateUrl: './users.component.html',
  styles: ``,
})
export class UsersComponent {
  users$: Observable<UserType[]>
  total$: Observable<number>

  @ViewChildren(NgbdSortableHeader) headers!: QueryList<
    NgbdSortableHeader<UserType>
  >

  constructor(public tableService: TableService<UserType>) {
    this.users$ = tableService.items$
    this.total$ = tableService.total$
  }

  ngOnInit(): void {
    this.tableService.setItems(UserData, 10)
  }

  onSort(event: SortEvent<UserType>) {
    const { column, direction } = event
    // resetting other headers
    this.headers.forEach((header) => {
      if (header.sortable !== column) {
        header.direction = ''
      }
    })

    this.tableService.sortColumn = column
    this.tableService.sortDirection = direction
  }
}
