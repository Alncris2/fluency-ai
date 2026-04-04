import { Component } from '@angular/core'
import { ProjectData } from './data'
import { ProjectCardComponent } from './components/project-card/project-card.component'

@Component({
  selector: 'app-project',
  standalone: true,
  imports: [ProjectCardComponent],
  templateUrl: './project.component.html',
  styles: ``,
})
export class ProjectComponent {
  ProjectList = ProjectData
}
