import { Routes } from '@angular/router';
import { authGuard } from './guards/auth.guard';

export const routes: Routes = [
  {
    path: '',
    redirectTo: '/products',
    pathMatch: 'full',
  },
  {
    path: 'login',
    loadComponent: () => import('./components/auth/login/login.component').then((m) => m.LoginComponent),
  },
  {
    path: 'register',
    loadComponent: () => import('./components/auth/register/register.component').then((m) => m.RegisterComponent),
  },
  {
    path: 'products',
    canActivate: [authGuard],
    children: [
      {
        path: '',
        loadComponent: () => import('./components/products/product-list/product-list.component').then((m) => m.ProductListComponent),
      },
      {
        path: 'import',
        loadComponent: () => import('./components/products/product-import/product-import.component').then((m) => m.ProductImportComponent),
      },
      {
        path: ':id',
        loadComponent: () => import('./components/products/product-detail/product-detail.component').then((m) => m.ProductDetailComponent),
      },
    ],
  },
  {
    path: '**',
    redirectTo: '/products',
  },
];