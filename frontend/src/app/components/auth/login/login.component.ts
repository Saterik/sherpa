import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { AuthService } from '@app/services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
  ],
  template: `
    <div class="auth-container">
      <mat-card class="auth-card">
        <mat-card-header>
          <div class="auth-header">
            <mat-icon class="auth-icon">lock_outline</mat-icon>
            <div>
              <mat-card-title>Войти</mat-card-title>
              <mat-card-subtitle>Войдите в свой аккаунт</mat-card-subtitle>
            </div>
          </div>
        </mat-card-header>

        <mat-card-content>
          <form [formGroup]="loginForm" (ngSubmit)="onSubmit()" class="auth-form">
            <mat-form-field appearance="outline" class="full-width" subscriptSizing="dynamic">
              <mat-label>Адрес электронной почты</mat-label>
              <input matInput type="email" formControlName="email" placeholder="Введите свой email" required autocomplete="email">
              <mat-icon matPrefix>mail_outline</mat-icon>
              @if (loginForm.get('email')?.hasError('required') && loginForm.get('email')?.touched) {
                <mat-error>Email обязателен</mat-error>
              }
              @if (loginForm.get('email')?.hasError('email') && loginForm.get('email')?.touched) {
                <mat-error>Введите корректный email</mat-error>
              }
            </mat-form-field>

            <mat-form-field appearance="outline" class="full-width" subscriptSizing="dynamic">
              <mat-label>Пароль</mat-label>
              <input matInput [type]="hidePassword ? 'password' : 'text'" formControlName="password" placeholder="Введите свой пароль" required autocomplete="current-password">
              <mat-icon matPrefix>lock_outline</mat-icon>
              <button mat-icon-button matSuffix type="button" (click)="hidePassword = !hidePassword" [attr.aria-label]="'Переключить видимость пароля'">
                <mat-icon>{{ hidePassword ? 'visibility_off' : 'visibility' }}</mat-icon>
              </button>
              @if (loginForm.get('password')?.hasError('required') && loginForm.get('password')?.touched) {
                <mat-error>Пароль обязателен</mat-error>
              }
              @if (loginForm.get('password')?.hasError('minlength') && loginForm.get('password')?.touched) {
                <mat-error>Пароль должен содержать минимум 6 символов</mat-error>
              }
            </mat-form-field>

            <div class="form-options">
              <label class="remember-me">
                <input type="checkbox" formControlName="rememberMe">
                <span>Запомнить меня</span>
              </label>
              <a routerLink="/forgot-password" class="forgot-password">Забыли пароль?</a>
            </div>

            <button mat-raised-button color="primary" type="submit" class="full-width submit-btn" [disabled]="loginForm.invalid || loading">
              @if (loading) {
                <mat-spinner diameter="20"></mat-spinner>
              } @else {
                <ng-container>
                  <span>Войти</span>
                  <mat-icon>arrow_forward</mat-icon>
                </ng-container>
              }
            </button>
          </form>
        </mat-card-content>

        <mat-card-actions>
          <div class="auth-footer">
            <p>У вас нет аккаунта?</p>
            <a routerLink="/register" class="register-link">Зарегистрироваться</a>
          </div>
        </mat-card-actions>
      </mat-card>
    </div>
  `,
  styles: [`
    .auth-container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: calc(100vh - 64px);
      padding: 24px 16px;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
    }

    .auth-card {
      width: 100%;
      max-width: 400px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
      border-radius: 16px;
      overflow: hidden;
    }

    .auth-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 8px;
    }

    .auth-icon {
      font-size: 32px;
      width: 32px;
      height: 32px;
      color: #3f51b5;
      background: linear-gradient(135deg, #e8eaf6 0%, #c5cae9 100%);
      border-radius: 12px;
      padding: 8px;
      flex-shrink: 0;
    }

    .auth-form {
      display: flex;
      flex-direction: column;
      gap: 16px;
      margin-top: 8px;
    }

    .full-width {
      width: 100%;
    }

    ::ng-deep .mat-mdc-form-field {
      --mdc-outlined-text-field-container-shape: 10px;
    }

    ::ng-deep .mat-mdc-form-field .mdc-notched-outline__leading,
    ::ng-deep .mat-mdc-form-field .mdc-notched-outline__trailing,
    ::ng-deep .mat-mdc-form-field .mdc-notched-outline__notch {
      border-color: rgba(0, 0, 0, 0.12);
    }

    ::ng-deep .mat-mdc-form-field.mat-focused .mdc-notched-outline__leading,
    ::ng-deep .mat-mdc-form-field.mat-focused .mdc-notched-outline__trailing,
    ::ng-deep .mat-mdc-form-field.mat-focused .mdc-notched-outline__notch {
      border-color: #3f51b5;
      border-width: 2px;
    }

    ::ng-deep .mat-mdc-text-field-wrapper {
      background: #fafafa;
      border-radius: 10px;
    }

    ::ng-deep .mat-mdc-form-field.mat-focused .mat-mdc-text-field-wrapper {
      background: white;
    }

    ::ng-deep .mat-mdc-form-field .mat-mdc-form-field-label {
      color: rgba(0, 0, 0, 0.54);
    }

    ::ng-deep .mat-mdc-form-field.mat-focused .mat-mdc-form-field-label {
      color: #3f51b5;
    }

    ::ng-deep .mat-mdc-form-field .mat-mdc-form-field-icon-prefix {
      color: rgba(0, 0, 0, 0.38);
    }

    ::ng-deep .mat-mdc-form-field.mat-focused .mat-mdc-form-field-icon-prefix {
      color: #3f51b5;
    }

    ::ng-deep .mat-mdc-form-field .mat-mdc-form-field-hint,
    ::ng-deep .mat-mdc-form-field .mat-mdc-form-field-error {
      margin-top: 4px;
    }

    ::ng-deep .mat-mdc-form-field-subscript {
      display: none;
    }

    .form-options {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-top: -4px;
    }

    .remember-me {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      font-size: 0.875rem;
      color: rgba(0, 0, 0, 0.6);
      white-space: nowrap;
    }

    .remember-me input[type="checkbox"] {
      width: 18px;
      height: 18px;
      accent-color: #3f51b5;
      flex-shrink: 0;
    }

    .forgot-password {
      color: #3f51b5;
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      white-space: nowrap;
    }

    .forgot-password:hover {
      text-decoration: underline;
    }

    .submit-btn {
      height: 48px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 4px;
      transition: all 0.2s ease;
    }

    .submit-btn:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(63, 81, 181, 0.4);
    }

    .submit-btn mat-spinner {
      margin: 0;
    }

    .auth-footer {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      margin: 0;
      padding: 16px 0 0;
    }

    .auth-footer p {
      margin: 0;
      color: rgba(0, 0, 0, 0.6);
      font-size: 0.9rem;
    }

    .register-link {
      color: #3f51b5;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      transition: color 0.2s;
    }

    .register-link:hover {
      color: #303f9f;
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      .auth-container {
        padding: 16px 12px;
        min-height: calc(100vh - 56px);
      }

      .auth-card {
        margin: 0;
        max-width: none;
        border-radius: 12px;
      }

      .auth-header {
        gap: 10px;
      }

      .auth-icon {
        font-size: 28px;
        width: 28px;
        height: 28px;
        padding: 6px;
        border-radius: 10px;
      }

      .form-options {
        flex-direction: column;
        gap: 8px;
        align-items: flex-start;
      }

      .remember-me,
      .forgot-password {
        width: 100%;
        text-align: left;
      }

      .submit-btn {
        height: 44px;
      }
    }

    @media (max-width: 360px) {
      .auth-container {
        padding: 12px 8px;
      }

      .auth-form {
        gap: 12px;
      }
    }
  `],
})
export class LoginComponent implements OnInit {
  loginForm: FormGroup;
  hidePassword = true;
  loading = false;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private snackBar: MatSnackBar
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      rememberMe: [false],
    });
  }

  ngOnInit(): void {
    if (this.authService.isLoggedIn()) {
      this.router.navigate(['/products']);
    }
  }

  onSubmit(): void {
    if (this.loginForm.valid) {
      this.loading = true;
      this.authService.login(this.loginForm.value).subscribe({
        next: () => {
          this.loading = false;
          this.snackBar.open('Добро пожаловать!', 'Закрыть', { duration: 3000 });
          this.router.navigate(['/products']);
        },
        error: (error: unknown) => {
          this.loading = false;
          const err = error as { error?: { error?: { message?: string } } };
          const message = err.error?.error?.message || 'Ошибка входа. Попробуйте снова.';
          this.snackBar.open(message, 'Закрыть', { duration: 5000 });
        },
      });
    }
  }
}