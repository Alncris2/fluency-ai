import { Injectable } from '@angular/core'
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
} from '@angular/common/http'
import { Observable } from 'rxjs'
import { CookieService } from 'ngx-cookie-service'

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(private cookieService: CookieService) {}

  intercept(req: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    const hasFluencySession = this.cookieService.check('_FLUENCY_SESSION_')
    const token = localStorage.getItem('fluency_token')

    if (hasFluencySession && token) {
      const authReq = req.clone({
        setHeaders: { Authorization: `Bearer ${token}` },
      })
      return next.handle(authReq)
    }

    return next.handle(req)
  }
}
