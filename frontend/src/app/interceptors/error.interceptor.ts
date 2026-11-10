import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { catchError, switchMap, throwError, of } from 'rxjs';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AuthService } from '../services/auth.service';
import { Router } from '@angular/router';

export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  const snackBar = inject(MatSnackBar);
  const authService = inject(AuthService);
  const router = inject(Router);

  return next(req).pipe(
    catchError((error) => {
      if (error.status === 401) {
        const refreshToken = localStorage.getItem('refresh_token');
        
        if (refreshToken) {
          return authService.refreshToken().pipe(
            switchMap(() => {
              const newToken = authService.getToken();
              if (newToken) {
                const authReq = req.clone({
                  setHeaders: {
                    Authorization: `Bearer ${newToken}`,
                  },
                });
                return next(authReq);
              }
              return throwError(() => error);
            }),
            catchError(() => {
              authService.logout();
              router.navigate(['/login']);
              return throwError(() => error);
            })
          );
        } else {
          authService.logout();
          router.navigate(['/login']);
        }
      } else if (error.status === 403) {
        snackBar.open('Access denied', 'Close', { duration: 3000 });
      } else if (error.status === 500) {
        snackBar.open('Server error. Please try again later.', 'Close', { duration: 5000 });
      } else if (error.status === 0) {
        snackBar.open('Network error. Please check your connection.', 'Close', { duration: 5000 });
      } else if (error.error?.error?.message) {
        snackBar.open(error.error.error.message, 'Close', { duration: 3000 });
      }

      return throwError(() => error);
    })
  );
};