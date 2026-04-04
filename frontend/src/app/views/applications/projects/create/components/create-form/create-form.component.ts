import { DatepickerDirective } from '@/app/core/directive/datepickr.directive'
import { Component } from '@angular/core'

@Component({
  selector: 'create-form',
  standalone: true,
  imports: [DatepickerDirective],
  templateUrl: './create-form.component.html',
  styles: ``,
})
export class CreateFormComponent {}
