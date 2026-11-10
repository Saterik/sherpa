import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ProductImportComponent } from './product-import.component';
import { ImportService } from '../../../../services/import.service';
import { of, throwError } from 'rxjs';
import { ImportJob, ImportReport } from '../../../../models';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { MatSnackBarModule } from '@angular/material/snack-bar';

describe('ProductImportComponent', () => {
  let component: ProductImportComponent;
  let fixture: ComponentFixture<ProductImportComponent>;
  let importService: jasmine.SpyObj<ImportService>;

  const mockJob: ImportJob = {
    job_id: 'import-test-123',
    status: 'queued',
    progress: 0,
    message: 'Import job queued for processing',
  };

  const mockReport: ImportReport = {
    job_id: 'import-test-123',
    errors: [],
    stats: {
      total: 10,
      created: 8,
      updated: 2,
      failed: 0,
    },
  };

  beforeEach(async () => {
    const importServiceSpy = jasmine.createSpyObj('ImportService', ['import', 'getStatus', 'getReport']);
    importServiceSpy.import.and.returnValue(of(mockJob));
    importServiceSpy.getStatus.and.returnValue(of({ ...mockJob, status: 'completed', progress: 100 }));
    importServiceSpy.getReport.and.returnValue(of(mockReport));

    await TestBed.configureTestingModule({
      imports: [
        ProductImportComponent,
        NoopAnimationsModule,
        MatSnackBarModule,
      ],
      providers: [
        { provide: ImportService, useValue: importServiceSpy },
      ],
    }).compileComponents();

    importService = TestBed.inject(ImportService) as jasmine.SpyObj<ImportService>;
    fixture = TestBed.createComponent(ProductImportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should handle file selection', () => {
    const file = new File(['test content'], 'test.xlsx', { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const event = { target: { files: [file] } } as unknown as Event;
    
    component.onFileSelected(event);
    
    expect(component.selectedFile).toBe(file);
  });

  it('should reject non-xlsx files', () => {
    const file = new File(['test content'], 'test.csv', { type: 'text/csv' });
    const event = { target: { files: [file] } } as unknown as Event;
    
    component.onFileSelected(event);
    
    expect(component.selectedFile).toBeNull();
  });

  it('should reject files larger than 10MB', () => {
    const largeFile = new File(['x'.repeat(11 * 1024 * 1024)], 'large.xlsx', { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const event = { target: { files: [largeFile] } } as unknown as Event;
    
    component.onFileSelected(event);
    
    expect(component.selectedFile).toBeNull();
  });

  it('should start import and poll for status', () => {
    component.selectedFile = new File(['test'], 'test.xlsx', { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    
    component.startImport();
    
    expect(importService.import).toHaveBeenCalled();
    expect(component.importJob).toEqual(mockJob);
  });

  it('should format file size correctly', () => {
    expect(component.formatFileSize(0)).toBe('0 Bytes');
    expect(component.formatFileSize(1024)).toBe('1 KB');
    expect(component.formatFileSize(1024 * 1024)).toBe('1 MB');
    expect(component.formatFileSize(1024 * 1024 * 1024)).toBe('1 GB');
  });
});