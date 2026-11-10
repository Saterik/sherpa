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
import { MatCheckboxModule } from '@angular/material/checkbox';
import { AuthService } from '@app/services/auth.service';

@Component({
  selector: 'app-register',
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
    MatCheckboxModule,
  ],
  template: `
    <div class="auth-container">
      <mat-card class="auth-card">
        <mat-card-header>
          <div class="auth-header">
            <mat-icon class="auth-icon">person_add_outlined</mat-icon>
            <div>
              <mat-card-title>Зарегистрироваться</mat-card-title>
              <mat-card-subtitle>Зарегистрируйтесь, чтобы начать</mat-card-subtitle>
            </div>
          </div>
        </mat-card-header>

        <mat-card-content>
          <form [formGroup]="registerForm" (ngSubmit)="onSubmit()" class="auth-form">
            <mat-form-field appearance="outline" class="full-width" subscriptSizing="dynamic">
              <mat-label>Полное имя</mat-label>
              <input matInput type="text" formControlName="name" placeholder="Введите ваше полное имя" autocomplete="name">
              <mat-icon matPrefix>person_outline</mat-icon>
              @if (registerForm.get('name')?.hasError('required') && registerForm.get('name')?.touched) {
                <mat-error>Полное имя обязательно</mat-error>
              }
              @if (registerForm.get('name')?.hasError('minlength') && registerForm.get('name')?.touched) {
                <mat-error>Имя должно содержать минимум 2 символа</mat-error>
              }
            </mat-form-field>

            <mat-form-field appearance="outline" class="full-width" subscriptSizing="dynamic">
              <mat-label>Адрес электронной почты</mat-label>
              <input matInput type="email" formControlName="email" placeholder="Введите свой адрес электронной почты" required autocomplete="email">
              <mat-icon matPrefix>mail_outline</mat-icon>
              @if (registerForm.get('email')?.hasError('required') && registerForm.get('email')?.touched) {
                <mat-error>Email обязателен</mat-error>
              }
              @if (registerForm.get('email')?.hasError('email') && registerForm.get('email')?.touched) {
                <mat-error>Введите корректный email</mat-error>
              }
            </mat-form-field>

            <mat-form-field appearance="outline" class="full-width" subscriptSizing="dynamic">
              <mat-label>Пароль</mat-label>
              <input matInput [type]="hidePassword ? 'password' : 'text'" formControlName="password" placeholder="Создать пароль" required autocomplete="new-password">
              <mat-icon matPrefix>lock_outline</mat-icon>
              <button mat-icon-button matSuffix type="button" (click)="hidePassword = !hidePassword" [attr.aria-label]="'Переключить видимость пароля'">
                <mat-icon>{{ hidePassword ? 'visibility_off' : 'visibility' }}</mat-icon>
              </button>
              @if (registerForm.get('password')?.hasError('required') && registerForm.get('password')?.touched) {
                <mat-error>Пароль обязателен</mat-error>
              }
              @if (registerForm.get('password')?.hasError('minlength') && registerForm.get('password')?.touched) {
                <mat-error>Пароль должен содержать минимум 6 символов</mat-error>
              }
            </mat-form-field>

            <mat-form-field appearance="outline" class="full-width" subscriptSizing="dynamic">
              <mat-label>Подтвердите пароль</mat-label>
              <input matInput [type]="hideConfirmPassword ? 'password' : 'text'" formControlName="confirmPassword" placeholder="Подтвердите свой пароль" required autocomplete="new-password">
              <mat-icon matPrefix>lock_outline</mat-icon>
              <button mat-icon-button matSuffix type="button" (click)="hideConfirmPassword = !hideConfirmPassword" [attr.aria-label]="'Переключить видимость пароля'">
                <mat-icon>{{ hideConfirmPassword ? 'visibility_off' : 'visibility' }}</mat-icon>
              </button>
              @if (registerForm.get('confirmPassword')?.hasError('required') && registerForm.get('confirmPassword')?.touched) {
                <mat-error>Подтвердите пароль</mat-error>
              }
              @if (registerForm.hasError('passwordMismatch') && registerForm.get('confirmPassword')?.touched) {
                <mat-error>Пароли не совпадают</mat-error>
              }
            </mat-form-field>

            <div class="terms-agreement">
              <label class="terms-checkbox-wrapper">
                <input type="checkbox" formControlName="agreeTerms" class="terms-checkbox-input" required>
                <span class="terms-checkbox-checkmark"></span>
                <span class="terms-text">
                  Я согласен с
                  <a href="#" class="terms-link" target="_blank" rel="noopener">Условиями предоставления услуг</a>
                  и
                  <a href="#" class="terms-link" target="_blank" rel="noopener">Политикой конфиденциальности</a>
                </span>
              </label>
              @if (registerForm.get('agreeTerms')?.hasError('required') && registerForm.get('agreeTerms')?.touched) {
                <div class="terms-error">Необходимо согласие с условиями</div>
              }
            </div>

            <button mat-raised-button color="primary" type="submit" class="full-width submit-btn" [disabled]="registerForm.invalid || loading">
              @if (loading) {
                <mat-spinner diameter="20"></mat-spinner>
              } @else {
                <ng-container>
                  <span>Зарегистрироваться</span>
                  <mat-icon>arrow_forward</mat-icon>
                </ng-container>
              }
            </button>
          </form>
        </mat-card-content>

        <mat-card-actions>
          <div class="auth-footer">
            <p>У вас уже есть аккаунт?</p>
            <a routerLink="/login" class="login-link">Войти</a>
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

    .terms-agreement {
      margin-top: -4px;
    }

    .terms-checkbox-wrapper {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      cursor: pointer;
      font-size: 0.8125rem;
      color: rgba(0, 0, 0, 0.6);
      line-height: 1.5;
    }

    .terms-checkbox-input {
      position: absolute;
      opacity: 0;
      width: 0;
      height: 0;
    }

    .terms-checkbox-checkmark {
      width: 18px;
      height: 18px;
      border: 2px solid rgba(0, 0, 0, 0.38);
      border-radius: 4px;
      background: #fafafa;
      flex-shrink: 0;
      margin-top: 2px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.15s ease;
    }

    .terms-checkbox-wrapper:hover .terms-checkbox-checkmark {
      border-color: #3f51b5;
    }

    .terms-checkbox-input:checked + .terms-checkbox-checkmark {
      background: #3f51b5;
      border-color: #3f51b5;
    }

    .terms-checkbox-input:checked + .terms-checkbox-checkmark::after {
      content: '';
      width: 5px;
      height: 9px;
      border: solid white;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg) translateY(-1px);
    }

    .terms-checkbox-input:focus-visible + .terms-checkbox-checkmark {
      outline: 2px solid #3f51b5;
      outline-offset: 2px;
    }

    .terms-text {
      line-height: 1.5;
    }

    .terms-link {
      color: #3f51b5;
      text-decoration: none;
      font-weight: 500;
    }

    .terms-link:hover {
      text-decoration: underline;
    }

    .terms-error {
      margin-top: 8px;
      margin-left: 28px;
      color: #dc2626;
      font-size: 0.75rem;
      line-height: 1.4;
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

    .login-link {
      color: #3f51b5;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      transition: color 0.2s;
    }

    .login-link:hover {
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

      .terms-checkbox-wrapper {
        font-size: 0.75rem;
        gap: 8px;
      }

      .terms-checkbox-checkmark {
        width: 16px;
        height: 16px;
      }

      .terms-error {
        margin-left: 24px;
        font-size: 0.7rem;
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

      .terms-checkbox-wrapper {
        font-size: 0.7rem;
      }
    }
  `],
})
export class RegisterComponent implements OnInit {
  registerForm: FormGroup;
  hidePassword = true;
  hideConfirmPassword = true;
  loading = false;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private snackBar: MatSnackBar
  ) {
    this.registerForm = this.fb.group({
      name: ['', [Validators.required, Validators.minLength(2)]],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      confirmPassword: ['', [Validators.required]],
      agreeTerms: [false, [Validators.requiredTrue]],
    }, { validators: this.passwordMatchValidator });
  }

  ngOnInit(): void {
    if (this.authService.isLoggedIn()) {
      this.router.navigate(['/products']);
    }
  }

  passwordMatchValidator(form: FormGroup) {
    const password = form.get('password');
    const confirmPassword = form.get('confirmPassword');

    if (password && confirmPassword && password.value !== confirmPassword.value) {
      return { passwordMismatch: true };
    }
    return null;
  }

  onSubmit(): void {
    if (this.registerForm.valid) {
      this.loading = true;
      const { confirmPassword, agreeTerms, ...userData } = this.registerForm.value;
      this.authService.register(userData).subscribe({
        next: () => {
          this.loading = false;
          this.snackBar.open('Аккаунт успешно создан!', 'Закрыть', { duration: 3000 });
          this.router.navigate(['/products']);
        },
        error: (error: unknown) => {
          this.loading = false;
          const err = error as { error?: { error?: { message?: string } } };
          const message = err.error?.error?.message || 'Ошибка регистрации. Попробуйте снова.';
          this.snackBar.open(message, 'Закрыть', { duration: 5000 });
        },
      });
    }
  }
}