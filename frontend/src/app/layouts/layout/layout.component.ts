import { Component, inject } from '@angular/core'
import { TopbarComponent } from '../topbar/topbar.component'
import { SidebarComponent } from '../sidebar/sidebar.component'
import { RouterModule } from '@angular/router'
import { FooterComponent } from '../footer/footer.component'
import { Store } from '@ngrx/store'
import { VerticalComponent } from '../vertical/vertical.component'

@Component({
  selector: 'app-layout',
  standalone: true,
  imports: [
    TopbarComponent,
    SidebarComponent,
    RouterModule,
    FooterComponent,
    VerticalComponent,
  ],
  templateUrl: './layout.component.html',
  styles: ``,
})
export class LayoutComponent {
  layoutType: any

  private store = inject(Store)

  ngOnInit(): void {
    this.store.select('layout').subscribe((data) => {
      this.layoutType = data.LAYOUT
      document.documentElement.setAttribute('data-bs-theme', data.LAYOUT_THEME)
      document.body.setAttribute('data-sidebar-size', data.MENU_SIZE)
      document.documentElement.setAttribute(
        'data-startbar',
        data.STARTBAR_COLOR
      )
    })
  }
}
