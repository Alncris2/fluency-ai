import { ListreeDirective } from '@/app/core/directive/listree.directive'
import { Component } from '@angular/core'

@Component({
  selector: 'app-treeview',
  standalone: true,
  imports: [ListreeDirective],
  templateUrl: './treeview.component.html',
  styles: ``,
})
export class TreeviewComponent {}
