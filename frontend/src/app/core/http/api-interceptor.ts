import { HttpInterceptorFn } from '@angular/common/http';

export const apiInterceptor: HttpInterceptorFn = (req, next) => {
  const cloned = req.clone({
    withCredentials: true, // necessário para Sanctum SPA (cookie-based)
  });
  return next(cloned);
};
