import { MagicDirective } from '@/app/core/directive/magic.directive'
import { Component } from '@angular/core'
import { SimplebarAngularModule } from 'simplebar-angular'

@Component({
  selector: 'app-animation',
  standalone: true,
  imports: [MagicDirective, SimplebarAngularModule],
  templateUrl: './animation.component.html',
  styles: ``,
})
export class AnimationComponent {}
