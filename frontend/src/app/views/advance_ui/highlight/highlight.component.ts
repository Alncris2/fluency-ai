import { Component, type AfterViewInit } from '@angular/core'
import * as Prism from 'prismjs'
@Component({
  selector: 'app-highlight',
  standalone: true,
  imports: [],
  templateUrl: './highlight.component.html',
  styles: ``,
})
export class HighlightComponent implements AfterViewInit {
  ngAfterViewInit(): void {
    Prism.highlightAll()
  }
}
