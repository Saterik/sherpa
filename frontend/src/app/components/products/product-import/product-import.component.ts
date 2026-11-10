import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatChipsModule } from '@angular/material/chips';
import { MatDividerModule } from '@angular/material/divider';
import { MatListModule } from '@angular/material/list';
import { ImportService } from '@app/services/import.service';
import { ImportJob, ImportReport } from '@app/models';

@Component({
  selector: 'app-product-import',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatProgressBarModule,
    MatChipsModule,
    MatDividerModule,
    MatListModule,
    MatSnackBarModule,
  ],
  template: `
    <div class="import-container">
      <div class="page-header">
        <div>
          <h1>Import Products</h1>
          <p class="subtitle">Upload an Excel (.xlsx) file to import products</p>
        </div>
        <button mat-button routerLink="/products">
          <mat-icon>arrow_back</mat-icon>
          Back to List
        </button>
      </div>

      <!-- Upload Area -->
      <mat-card class="upload-card">
        <mat-card-content>
          @if (!selectedFile && !importJob) {
            <div class="drop-zone" (click)="fileInput.click()" (dragover)="onDragOver($event)" (dragleave)="onDragLeave($event)" (drop)="onDrop($event)" [class.drag-active]="dragActive">
              <input #fileInput type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" (change)="onFileSelected($event)" hidden>
              
              <mat-icon class="drop-icon">cloud_upload</mat-icon>
              <h3>Drop your .xlsx file here</h3>
              <p>or click to browse</p>
              <p class="file-hint">Maximum file size: 10MB</p>
            </div>
          }

          @if (selectedFile) {
            <div class="file-selected">
              <div class="file-info">
                <mat-icon>description</mat-icon>
                <div>
                  <span class="file-name">{{ selectedFile.name }}</span>
                  <span class="file-size">{{ formatFileSize(selectedFile.size) }}</span>
                </div>
              </div>
              <div class="file-actions">
                <button mat-stroked-button (click)="clearFile()">
                  <mat-icon>close</mat-icon>
                  Remove
                </button>
                <button mat-raised-button color="primary" (click)="startImport()" [disabled]="importing">
                  <mat-icon>send</mat-icon>
                  Import
                </button>
              </div>
            </div>
          }

          @if (importJob) {
            <div class="import-progress">
              <div class="progress-header">
                <h3>Import in Progress</h3>
                <mat-chip [color]="getStatusColor(importJob.status)">{{ importJob.status }}</mat-chip>
              </div>
              
              <mat-progress-bar mode="determinate" [value]="importJob.progress"></mat-progress-bar>
              <p class="progress-message">{{ importJob.message }}</p>
              
              @if (importJob.status === 'completed') {
                <mat-divider class="mt-16"></mat-divider>
                <div class="results">
                  <h4>Import Results</h4>
                  <div class="stats">
                    <div class="stat">
                      <span class="stat-value">{{ importReport?.stats?.total ?? 0 }}</span>
                      <span class="stat-label">Total</span>
                    </div>
                    <div class="stat success">
                      <span class="stat-value">{{ importReport?.stats?.created ?? 0 }}</span>
                      <span class="stat-label">Created</span>
                    </div>
                    <div class="stat warning">
                      <span class="stat-value">{{ importReport?.stats?.updated ?? 0 }}</span>
                      <span class="stat-label">Updated</span>
                    </div>
                    <div class="stat error">
                      <span class="stat-value">{{ importReport?.stats?.failed ?? 0 }}</span>
                      <span class="stat-label">Failed</span>
                    </div>
                  </div>
                  
                  @if (importReport && importReport.errors.length > 0) {
                    <mat-divider class="mt-16"></mat-divider>
                    <h4>Errors ({{ importReport.errors.length }})</h4>
                    <mat-list class="errors-list">
                      @for (error of importReport.errors; track error.row) {
                        <mat-list-item>
                          <div matListItemTitle>Row {{ error.row }}: {{ error.external_code }}</div>
                          <div matListItemLine>{{ error.error }}</div>
                          @if (error.errors?.length) {
                            <div class="sub-errors">
                              @for (subError of error.errors; track subError) {
                                <div class="sub-error">• {{ subError }}</div>
                              }
                            </div>
                          }
                        </mat-list-item>
                      }
                    </mat-list>
                  }
                </div>
                
                <div class="actions mt-24">
                  <button mat-raised-button color="primary" (click)="resetImport()">
                    <mat-icon>refresh</mat-icon>
                    Import Another File
                  </button>
                  <button mat-button routerLink="/products">
                    <mat-icon>visibility</mat-icon>
                    View Products
                  </button>
                </div>
              }
            </div>
          }
        </mat-card-content>
      </mat-card>

      <!-- Instructions -->
      <mat-card class="instructions-card">
        <mat-card-header>
          <mat-card-title>Import Instructions</mat-card-title>
        </mat-card-header>
        <mat-card-content>
          <ul class="instructions-list">
            <li>Prepare your Excel file (.xlsx format) with product data</li>
            <li>Required columns: <strong>UUID</strong> (external code), <strong>Наименование</strong> (name), <strong>Цена: Цена продажи</strong> (price)</li>
            <li>Optional columns: Description, SKU, Unit, Country, VAT, Brand, Barcode, etc.</li>
            <li>Additional attributes are automatically detected from columns with "Доп. поле:" prefix</li>
            <li>Images: Provide URLs in "Доп. поле: Ссылки на фото" column (comma-separated)</li>
            <li>Images will be downloaded and stored locally during import</li>
            <li>Duplicate products (same UUID) will be updated, not duplicated</li>
            <li>Invalid rows are skipped and reported without stopping the import</li>
          </ul>
        </mat-card-content>
      </mat-card>
    </div>
  `,
  styles: [`
    .import-container {
      display: flex;
      flex-direction: column;
      gap: 24px;
      max-width: 800px;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      flex-wrap: wrap;
      gap: 16px;
    }

    .page-header h1 {
      margin: 0;
      font-size: 1.75rem;
      font-weight: 500;
    }

    .subtitle {
      margin: 4px 0 0;
      color: rgba(0,0,0,0.6);
    }

    .upload-card {
      overflow: hidden;
    }

    .drop-zone {
      border: 2px dashed rgba(0,0,0,0.2);
      border-radius: 8px;
      padding: 48px 24px;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s;
      background: #fafafa;
    }

    .drop-zone:hover, .drop-zone.drag-active {
      border-color: #3f51b5;
      background: #e8eaf6;
    }

    .drop-icon {
      font-size: 64px;
      width: 64px;
      height: 64px;
      color: rgba(0,0,0,0.3);
      margin-bottom: 16px;
    }

    .drop-zone h3 {
      margin: 0 0 8px;
      font-weight: 500;
    }

    .drop-zone p {
      margin: 0 0 4px;
      color: rgba(0,0,0,0.6);
    }

    .file-hint {
      font-size: 0.875rem;
      color: rgba(0,0,0,0.4);
    }

    .file-selected {
      padding: 16px 0;
    }

    .file-info {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 16px;
      background: #f5f5f5;
      border-radius: 8px;
      margin-bottom: 16px;
    }

    .file-info mat-icon {
      font-size: 48px;
      width: 48px;
      height: 48px;
      color: #3f51b5;
    }

    .file-name {
      display: block;
      font-weight: 500;
      margin-bottom: 4px;
    }

    .file-size {
      font-size: 0.875rem;
      color: rgba(0,0,0,0.6);
    }

    .file-actions {
      display: flex;
      gap: 16px;
      justify-content: flex-end;
    }

    .import-progress {
      padding: 16px 0;
    }

    .progress-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }

    .progress-header h3 {
      margin: 0;
    }

    .progress-message {
      margin: 16px 0 0;
      color: rgba(0,0,0,0.6);
    }

    .results {
      margin-top: 24px;
    }

    .results h4 {
      margin: 0 0 16px;
    }

    .stats {
      display: flex;
      gap: 24px;
      flex-wrap: wrap;
    }

    .stat {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 16px;
      background: #f5f5f5;
      border-radius: 8px;
      min-width: 80px;
    }

    .stat.success {
      background: #e8f5e9;
      color: #2e7d32;
    }

    .stat.warning {
      background: #fff3e0;
      color: #ef6c00;
    }

    .stat.error {
      background: #fbe9e7;
      color: #c62828;
    }

    .stat-value {
      font-size: 1.5rem;
      font-weight: 600;
    }

    .stat-label {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .errors-list {
      max-height: 300px;
      overflow-y: auto;
    }

    .sub-errors {
      margin-top: 4px;
      padding-left: 16px;
    }

    .sub-error {
      font-size: 0.8rem;
      color: rgba(0,0,0,0.7);
    }

    .actions {
      display: flex;
      gap: 16px;
      justify-content: flex-end;
    }

    .mt-16 { margin-top: 16px; }
    .mt-24 { margin-top: 24px; }

    .instructions-card {
      background: #fafafa;
    }

    .instructions-list {
      margin: 0;
      padding-left: 20px;
      line-height: 1.8;
      color: rgba(0,0,0,0.7);
    }

    .instructions-list li {
      margin-bottom: 8px;
    }

    @media (max-width: 768px) {
      .page-header {
        flex-direction: column;
        align-items: stretch;
      }

      .stats {
        flex-direction: column;
      }

      .stat {
        flex-direction: row;
        justify-content: space-between;
        min-width: 0;
      }
    }
  `],
})
export class ProductImportComponent implements OnInit {
  selectedFile: File | null = null;
  dragActive = false;
  importing = false;
  importJob: ImportJob | null = null;
  importReport: ImportReport | null = null;
  pollInterval: any = null;

  constructor(
    private importService: ImportService,
    private router: Router,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {}

  ngOnDestroy(): void {
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
    }
  }

  onDragOver(event: DragEvent): void {
    event.preventDefault();
    event.stopPropagation();
    this.dragActive = true;
  }

  onDragLeave(event: DragEvent): void {
    event.preventDefault();
    event.stopPropagation();
    this.dragActive = false;
  }

  onDrop(event: DragEvent): void {
    event.preventDefault();
    event.stopPropagation();
    this.dragActive = false;

    const files = event.dataTransfer?.files;
    if (files && files.length > 0) {
      this.handleFile(files[0]);
    }
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.handleFile(input.files[0]);
    }
  }

  handleFile(file: File): void {
    if (!file.name.endsWith('.xlsx')) {
      this.snackBar.open('Please select a .xlsx file', 'Close', { duration: 3000 });
      return;
    }

    if (file.size > 10 * 1024 * 1024) {
      this.snackBar.open('File size exceeds 10MB limit', 'Close', { duration: 3000 });
      return;
    }

    this.selectedFile = file;
  }

  clearFile(): void {
    this.selectedFile = null;
  }

  startImport(): void {
    if (!this.selectedFile) return;

    this.importing = true;
    this.importService.import(this.selectedFile).subscribe({
      next: (job) => {
        this.importJob = job;
        this.importing = false;
        this.startPolling(job.job_id);
      },
      error: () => {
          this.stopPolling();
        },
    });
  }

  startPolling(jobId: string): void {
    this.pollInterval = setInterval(() => {
      this.importService.getStatus(jobId).subscribe({
        next: (job) => {
          this.importJob = job;
          
          if (job.status === 'completed' || job.status === 'failed') {
            this.stopPolling();
            this.loadReport(jobId);
          }
        },
        error: () => {
          this.stopPolling();
        },
      });
    }, 2000);
  }

  stopPolling(): void {
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
      this.pollInterval = null;
    }
  }

  loadReport(jobId: string): void {
    this.importService.getReport(jobId).subscribe({
      next: (report) => {
        this.importReport = report;
      },
      error: () => {
        // Report loading failed, but job status is available
      },
    });
  }

  resetImport(): void {
    this.selectedFile = null;
    this.importJob = null;
    this.importReport = null;
    this.stopPolling();
  }

  getStatusColor(status: string): 'primary' | 'accent' | 'warn' {
    switch (status) {
      case 'completed': return 'primary';
      case 'failed': return 'warn';
      case 'processing': return 'accent';
      default: return 'primary';
    }
  }

  formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }
}