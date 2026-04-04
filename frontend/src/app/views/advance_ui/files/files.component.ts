import { Component } from '@angular/core'
import { AudioList, DocumentList, FolderList, ImageList } from './data'
import {
  NgbDropdownModule,
  NgbNavModule,
  NgbProgressbarModule,
} from '@ng-bootstrap/ng-bootstrap'
import { CommonModule } from '@angular/common'

@Component({
  selector: 'app-files',
  standalone: true,
  imports: [
    NgbProgressbarModule,
    CommonModule,
    NgbNavModule,
    NgbDropdownModule,
  ],
  templateUrl: './files.component.html',
  styles: ``,
})
export class FilesComponent {
  folders = FolderList
  documents = DocumentList
  images = ImageList
  audioData = AudioList
}
