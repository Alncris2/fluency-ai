import { Component, Input } from '@angular/core'
import type { ClientType } from '../../data'
import { RouterModule } from '@angular/router'

@Component({
  selector: 'client-card',
  standalone: true,
  imports: [RouterModule],
  templateUrl: './client-card.component.html',
  styles: ``,
})
export class ClientCardComponent {
  @Input() client!: ClientType
}
