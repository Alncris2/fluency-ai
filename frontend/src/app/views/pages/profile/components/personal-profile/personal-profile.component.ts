import { ChartOptions } from '@/app/common/apexchart.model'
import { currency } from '@/app/common/constants'
import { Component } from '@angular/core'
import { NgApexchartsModule } from 'ng-apexcharts'

@Component({
  selector: 'app-personal-profile',
  standalone: true,
  imports: [NgApexchartsModule],
  templateUrl: './personal-profile.component.html',
  styles: ``,
})
export class PersonalProfileComponent {
  currency = currency
  completionChart: Partial<ChartOptions> = {
    series: [67],
    chart: {
      height: 170,
      type: 'radialBar',
      offsetY: -10,
    },
    colors: ['var(--bs-primary)'],
    plotOptions: {
      radialBar: {
        startAngle: -135,
        endAngle: 135,
        track: {
          background: ['rgba(42, 118, 244, .18)'],
        },
        dataLabels: {
          name: {
            fontSize: '13px',
            offsetY: 50,
          },
          value: {
            offsetY: 5,
            fontSize: '15px',
            formatter: function (val) {
              return val + '%'
            },
          },
        },
      },
    },

    stroke: {
      dashArray: 2,
    },
    labels: ['Compleation'],
  }
}
