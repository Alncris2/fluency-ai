import { LogoBoxComponent } from '@/app/components/logo-box/logo-box.component'
import { Component, inject } from '@angular/core'
import { NavigationEnd, Router, RouterModule } from '@angular/router'
import { SimplebarAngularModule } from 'simplebar-angular'
import { NgbCollapseModule, NgbCollapse } from '@ng-bootstrap/ng-bootstrap'
import { findAllParent, findMenuItem } from '@/app/core/helpers/utils'
import { CommonModule } from '@angular/common'
import { MenuItem } from '@/app/core/models/menu.model'
import { MENU_ITEMS } from '@/app/common/menu-items'
import { basePath } from '@/app/common/constants'

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [
    SimplebarAngularModule,
    LogoBoxComponent,
    RouterModule,
    NgbCollapseModule,
    CommonModule,
  ],
  templateUrl: './sidebar.component.html',
  styles: ``,
})
export class SidebarComponent {
  menuItems = MENU_ITEMS
  activeMenuItems: string[] = []
  router = inject(Router)

  trimmedURL = this.router.url?.replaceAll(
    basePath !== '' ? basePath + '/' : '',
    '/'
  )

  constructor() {
    this.router.events.forEach((event) => {
      if (event instanceof NavigationEnd) {
        this.trimmedURL = this.router.url?.replaceAll(
          basePath !== '' ? basePath + '/' : '',
          '/'
        )
        this._activateMenu()
        setTimeout(() => {
          // this.scrollToActive();
        }, 200)
      }
    })
  }

  ngOnInit() {
    this.initMenu()
  }

  ngAfterViewInit() {
    setTimeout(() => {
      this._activateMenu()
    })
  }

  initMenu(): void {
    this.menuItems = MENU_ITEMS
  }

  /**
   * toggles open menu
   * @param menuItem clicked menuitem
   * @param collapse collpase instance
   */
  toggleMenuItem(menuItem: MenuItem, collapse: NgbCollapse): void {
    collapse.toggle()
    let openMenuItems: string[]
    if (!menuItem.collapsed) {
      openMenuItems = [
        menuItem['key'],
        ...findAllParent(this.menuItems, menuItem),
      ]
      const collapseMenuItems = (menu: MenuItem) => {
        if (menu.subMenu) {
          menu.subMenu.forEach(collapseMenuItems)
        }
        if (!openMenuItems.includes(menu.key!)) {
          menu.collapsed = true
        }
      }

      this.menuItems.forEach(collapseMenuItems)
    }
  }

  _activateMenu(): void {
    const div = document.querySelector('.navbar-nav')
    let matchingMenuItem = null

    if (div) {
      let items: any = div.getElementsByClassName('nav-link')
      for (let i = 0; i < items.length; ++i) {
        if (this.trimmedURL === items[i].pathname) {
          matchingMenuItem = items[i]
          break
        }
      }

      if (matchingMenuItem) {
        const mid = matchingMenuItem.getAttribute('aria-controls')
        const activeMt = findMenuItem(this.menuItems, mid)

        if (activeMt) {
          const matchingObjs = [
            activeMt['key'],
            ...findAllParent(this.menuItems, activeMt),
          ]

          this.activeMenuItems = matchingObjs

          const collapseMenuItems = (menu: MenuItem) => {
            if (menu.subMenu) {
              menu.subMenu.forEach(collapseMenuItems)
            }
            menu.collapsed = !matchingObjs.includes(menu.key!)
          }

          this.menuItems.forEach(collapseMenuItems)
        }
      }
    }
  }

  hasSubmenu(menu: MenuItem): boolean {
    return menu.subMenu ? true : false
  }
}
