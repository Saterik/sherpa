import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Product, PaginatedResponse, ProductListParams } from '../models';

@Injectable({
  providedIn: 'root',
})
export class ProductService {
  private readonly apiUrl = `${environment.apiUrl}/products`;

  constructor(private http: HttpClient) {}

  getProducts(params: ProductListParams = {}): Observable<PaginatedResponse<Product>> {
    let httpParams = new HttpParams()
      .set('page', params.page?.toString() || '1')
      .set('limit', params.limit?.toString() || '20');

    if (params.search) {
      httpParams = httpParams.set('search', params.search);
    }
    if (params.price_min !== undefined) {
      httpParams = httpParams.set('price_min', params.price_min.toString());
    }
    if (params.price_max !== undefined) {
      httpParams = httpParams.set('price_max', params.price_max.toString());
    }

    return this.http.get<PaginatedResponse<Product>>(this.apiUrl, { params: httpParams });
  }

  getProduct(id: string): Observable<{ data: Product }> {
    return this.http.get<{ data: Product }>(`${this.apiUrl}/${id}`);
  }

  createProduct(product: Partial<Product>): Observable<{ data: Product; message: string }> {
    return this.http.post<{ data: Product; message: string }>(this.apiUrl, product);
  }

  updateProduct(id: string, product: Partial<Product>): Observable<{ data: Product; message: string }> {
    return this.http.put<{ data: Product; message: string }>(`${this.apiUrl}/${id}`, product);
  }

  deleteProduct(id: string): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.apiUrl}/${id}`);
  }
}