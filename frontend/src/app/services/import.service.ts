import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { ImportJob, ImportReport } from '../models';

@Injectable({
  providedIn: 'root',
})
export class ImportService {
  private readonly apiUrl = `${environment.apiUrl}/import`;

  constructor(private http: HttpClient) {}

  import(file: File): Observable<ImportJob> {
    const formData = new FormData();
    formData.append('file', file);

    return this.http.post<ImportJob>(this.apiUrl, formData);
  }

  getStatus(jobId: string): Observable<ImportJob> {
    return this.http.get<ImportJob>(`${this.apiUrl}/${jobId}/status`);
  }

  getReport(jobId: string): Observable<ImportReport> {
    return this.http.get<ImportReport>(`${this.apiUrl}/${jobId}/report`);
  }
}