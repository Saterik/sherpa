import { Component } from '@angular/core';
import { RouterOutlet, RouterModule } from '@angular/router';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatBadgeModule } from '@angular/material/badge';
import { MatDividerModule } from '@angular/material/divider';
import { AuthService } from './services/auth.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    CommonModule,
    RouterOutlet,
    RouterModule,
    MatToolbarModule,
    MatButtonModule,
    MatIconModule,
    MatMenuModule,
    MatBadgeModule,
    MatDividerModule,
  ],
  template: `
    <mat-toolbar color="primary" class="app-toolbar">
      <span class="toolbar-title" routerLink="/products">Sherpa Products</span>
      
      <span class="toolbar-spacer"></span>
      
      @if (authService.isLoggedIn()) {
        <nav>
          <a mat-button routerLink="/products" routerLinkActive="active">
            <mat-icon>inventory</mat-icon>
            Products
          </a>
          <a mat-button routerLink="/products/import" routerLinkActive="active">
            <mat-icon>upload</mat-icon>
            Import
          </a>
        </nav>
        
        <button mat-icon-button [matMenuTriggerFor]="userMenu">
          <mat-icon>account_circle</mat-icon>
        </button>
        <mat-menu #userMenu="matMenu">
          <span matMenuItem class="menu-user-info">
            {{ authService.getCurrentUser()?.name || authService.getCurrentUser()?.email }}
          </span>
          <mat-divider></mat-divider>
          <button mat-menu-item (click)="logout()">
            <mat-icon>logout</mat-icon>
            Logout
          </button>
        </mat-menu>
      } @else {
        <nav>
          <a mat-button routerLink="/login">Login</a>
          <a mat-button routerLink="/register" color="accent">Register</a>
        </nav>
      }
    </mat-toolbar>
    
    <main class="app-content">
      <router-outlet></router-outlet>
    </main>
  `,
  styles: [`
    .app-toolbar {
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .toolbar-title {
      font-size: 1.25rem;
      font-weight: 500;
      cursor: pointer;
      user-select: none;
    }
    
    .toolbar-spacer {
      flex: 1 1 auto;
    }
    
    nav {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    nav a {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    
    .menu-user-info {
      padding: 8px 16px;
      font-weight: 500;
    }
    
    .app-content {
      padding: 24px;
      max-width: 1400px;
      margin: 0 auto;
    }
    
    @media (max-width: 768px) {
      .app-content {
        padding: 16px;
      }
      
      .toolbar-title {
        font-size: 1rem;
      }
      
      nav a span {
        display: none;
      }
    }
  `],
})
export class AppComponent {
  constructor(public authService: AuthService) {}

  logout(): void {
    this.authService.logout();
  }
}