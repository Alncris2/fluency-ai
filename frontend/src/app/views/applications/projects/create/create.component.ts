import { Component } from '@angular/core'
import { CreateFormComponent } from './components/create-form/create-form.component'
import { CreateDetailComponent } from './components/create-detail/create-detail.component'

@Component({
  selector: 'app-create',
  standalone: true,
  imports: [CreateFormComponent, CreateDetailComponent],
  templateUrl: './create.component.html',
  styles: ``,
})
export class CreateComponent {}
