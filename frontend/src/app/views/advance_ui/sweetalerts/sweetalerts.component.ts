import { Component, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core'
import { SweetAlertService } from '@/app/core/service/sweet-alert.service'

@Component({
  selector: 'app-sweetalerts',
  standalone: true,
  imports: [],
  templateUrl: './sweetalerts.component.html',
  styles: ``,
  schemas: [CUSTOM_ELEMENTS_SCHEMA],
})
export class SweetalertsComponent {
  constructor(private sweetAlertService: SweetAlertService) {}

  showAlert(sub: string) {
    this.sweetAlertService.executeExample(sub)
  }
}
