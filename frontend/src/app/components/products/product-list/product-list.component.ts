import { Component, OnInit, ViewChild, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { Store, select } from '@ngrx/store';
import { Observable, Subject, combineLatest, debounceTime, distinctUntilChanged, takeUntil } from 'rxjs';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule, PageEvent, MatPaginator } from '@angular/material/paginator';
import { MatSortModule, Sort, MatSort } from '@angular/material/sort';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatChipsModule } from '@angular/material/chips';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatCardModule } from '@angular/material/card';
import { MatMenuModule } from '@angular/material/menu';
import { MatDividerModule } from '@angular/material/divider';

import { Product, ProductListParams } from '@app/models';
import { ProductService } from '@app/services/product.service';
import { loadProducts } from '@app/store/products/products.actions';
import { selectAllProducts, selectProductsLoading, selectTotalPages, selectTotalCount, selectCurrentPage, selectSearch, selectPriceMin, selectPriceMax } from '@app/store/products/products.selectors';

@Component({
  selector: 'app-product-list',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatTableModule,
    MatPaginatorModule,
    MatSortModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatChipsModule,
    MatTooltipModule,
    MatCardModule,
    MatMenuModule,
    MatDividerModule,
    RouterModule,
  ],
  template: `
    <div class="product-list-container">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>Products</h1>
          <p class="subtitle">Manage your product catalog</p>
        </div>
        <button mat-raised-button color="primary" routerLink="/products/import">
          <mat-icon>upload</mat-icon>
          Import Products
        </button>
      </div>

      <!-- Filters -->
      <mat-card class="filters-card">
        <mat-card-content>
          <div class="filters-row">
            <mat-form-field appearance="outline" class="filter-field">
              <mat-label>Search</mat-label>
              <input matInput [formControl]="searchControl" placeholder="Search by name, SKU, or code...">
              <mat-icon matPrefix>search</mat-icon>
            </mat-form-field>

            <mat-form-field appearance="outline" class="filter-field price-filter">
              <mat-label>Min Price</mat-label>
              <input matInput type="number" [formControl]="priceMinControl" placeholder="0" step="0.01" min="0">
              <span matPrefix>{{ currency }}&nbsp;</span>
            </mat-form-field>

            <mat-form-field appearance="outline" class="filter-field price-filter">
              <mat-label>Max Price</mat-label>
              <input matInput type="number" [formControl]="priceMaxControl" placeholder="100000" step="0.01" min="0">
              <span matPrefix>{{ currency }}&nbsp;</span>
            </mat-form-field>

            <button mat-stroked-button (click)="clearFilters()" [disabled]="!hasActiveFilters">
              <mat-icon>clear</mat-icon>
              Clear
            </button>
          </div>
        </mat-card-content>
      </mat-card>

      <!-- Table -->
      <mat-card class="table-card">
        @if (loading$ | async) {
          <div class="loading-overlay">
            <mat-spinner diameter="40"></mat-spinner>
          </div>
        }
        
        <div class="table-container">
          <table mat-table [dataSource]="(products$ | async) ?? []" matSort (matSortChange)="onSortChange($event)" class="product-table">
            
            <!-- Image Column -->
            <ng-container matColumnDef="image">
              <th mat-header-cell *matHeaderCellDef>Image</th>
              <td mat-cell *matCellDef="let product">
                @if (product.images?.length > 0) {
                  <img [src]="getImageUrl(product.images[0])" [alt]="product.name" class="product-thumbnail" loading="lazy">
                } @else {
                  <div class="no-image-placeholder">
                    <mat-icon>image_not_supported</mat-icon>
                  </div>
                }
              </td>
            </ng-container>

            <!-- Name Column -->
            <ng-container matColumnDef="name">
              <th mat-header-cell *matHeaderCellDef mat-sort-header>Name</th>
              <td mat-cell *matCellDef="let product">
                <a [routerLink]="['/products', product.id]" class="product-link">
                  <span class="product-name">{{ product.name }}</span>
                  <span class="product-code">{{ product.external_code }}</span>
                </a>
              </td>
            </ng-container>

            <!-- SKU Column -->
            <ng-container matColumnDef="sku">
              <th mat-header-cell *matHeaderCellDef mat-sort-header>SKU</th>
              <td mat-cell *matCellDef="let product">{{ product.sku || '-' }}</td>
            </ng-container>

            <!-- Price Column -->
            <ng-container matColumnDef="price">
              <th mat-header-cell *matHeaderCellDef mat-sort-header>Price</th>
              <td mat-cell *matCellDef="let product">
                <span class="price">{{ formatPrice(product.price) }}</span>
                @if (hasDiscount(product)) {
                  <mat-chip class="discount-chip" color="warn">{{ product.discount }}% OFF</mat-chip>
                }
              </td>
            </ng-container>

            <!-- Category Column -->
            <ng-container matColumnDef="category">
              <th mat-header-cell *matHeaderCellDef>Category</th>
              <td mat-cell *matCellDef="let product">
                @if (product.category) {
                  <mat-chip>{{ product.category }}</mat-chip>
                } @else {
                  <span class="text-muted">-</span>
                }
              </td>
            </ng-container>

            <!-- Brand Column -->
            <ng-container matColumnDef="brand">
              <th mat-header-cell *matHeaderCellDef>Brand</th>
              <td mat-cell *matCellDef="let product">{{ product.brand || '-' }}</td>
            </ng-container>

            <!-- Status Column -->
            <ng-container matColumnDef="status">
              <th mat-header-cell *matHeaderCellDef>Status</th>
              <td mat-cell *matCellDef="let product">
                <mat-chip [color]="product.is_archived ? 'warn' : 'primary'">
                  {{ product.is_archived ? 'Archived' : 'Active' }}
                </mat-chip>
              </td>
            </ng-container>

            <!-- Actions Column -->
            <ng-container matColumnDef="actions">
              <th mat-header-cell *matHeaderCellDef>Actions</th>
              <td mat-cell *matCellDef="let product">
                <button mat-icon-button [matMenuTriggerFor]="actionsMenu" aria-label="Actions">
                  <mat-icon>more_vert</mat-icon>
                </button>
                <mat-menu #actionsMenu="matMenu">
                  <button mat-menu-item [routerLink]="['/products', product.id]">
                    <mat-icon>visibility</mat-icon>
                    View
                  </button>
                  <button mat-menu-item (click)="editProduct(product)">
                    <mat-icon>edit</mat-icon>
                    Edit
                  </button>
                  <mat-divider></mat-divider>
                  <button mat-menu-item (click)="deleteProduct(product)" class="delete-action">
                    <mat-icon>delete</mat-icon>
                    Delete
                  </button>
                </mat-menu>
              </td>
            </ng-container>

            <tr mat-header-row *matHeaderRowDef="displayedColumns"></tr>
            <tr mat-row *matRowDef="let row; columns: displayedColumns;" (click)="navigateToDetail(row)"></tr>
          </table>
        </div>

        <!-- Empty State -->
        @if (!(loading$ | async) && (products$ | async)?.length === 0) {
          <div class="empty-state">
            <mat-icon>inventory_2</mat-icon>
            <h3>No products found</h3>
            <p>Get started by importing products from an Excel file</p>
            <button mat-raised-button color="primary" routerLink="/products/import">
              <mat-icon>upload</mat-icon>
              Import Products
            </button>
          </div>
        }

        <!-- Paginator -->
        <mat-paginator
          [length]="totalCount$ | async"
          [pageSize]="pageSize"
          [pageSizeOptions]="[10, 20, 50, 100]"
          [pageIndex]="(currentPage$ | async)! - 1"
          (page)="onPageChange($event)"
          showFirstLastButtons
          aria-label="Select page">
        </mat-paginator>
      </mat-card>
    </div>
  `,
  styles: [`
    .product-list-container {
      display: flex;
      flex-direction: column;
      gap: 24px;
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

    .filters-card {
      margin-bottom: 0;
    }

    .filters-row {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      align-items: flex-end;
    }

    .filter-field {
      flex: 1;
      min-width: 200px;
    }

    .price-filter {
      min-width: 140px;
      max-width: 200px;
    }

    .table-card {
      overflow: hidden;
    }

    .loading-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      background: rgba(255,255,255,0.8);
      z-index: 10;
    }

    .table-container {
      position: relative;
      overflow-x: auto;
    }

    .product-table {
      width: 100%;
      min-width: 800px;
    }

    .product-thumbnail {
      width: 48px;
      height: 48px;
      object-fit: cover;
      border-radius: 4px;
      border: 1px solid rgba(0,0,0,0.1);
    }

    .no-image-placeholder {
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f5f5f5;
      border-radius: 4px;
      color: rgba(0,0,0,0.3);
    }

    .product-link {
      text-decoration: none;
      color: inherit;
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .product-name {
      font-weight: 500;
    }

    .product-code {
      font-size: 0.75rem;
      color: rgba(0,0,0,0.5);
      font-family: monospace;
    }

    .price {
      font-weight: 600;
      font-size: 1rem;
    }

    .discount-chip {
      font-size: 0.65rem;
      height: 20px;
      margin-left: 8px;
    }

    .text-muted {
      color: rgba(0,0,0,0.4);
    }

    .empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 64px 24px;
      text-align: center;
      gap: 16px;
    }

    .empty-state mat-icon {
      font-size: 64px;
      width: 64px;
      height: 64px;
      color: rgba(0,0,0,0.2);
    }

    .empty-state h3 {
      margin: 0;
      font-weight: 500;
    }

    .empty-state p {
      margin: 0;
      color: rgba(0,0,0,0.6);
    }

    .delete-action {
      color: #f44336;
    }

    tr[mat-row] {
      cursor: pointer;
      transition: background-color 0.2s;
    }

    tr[mat-row]:hover {
      background-color: rgba(0,0,0,0.02);
    }

    @media (max-width: 768px) {
      .page-header {
        flex-direction: column;
        align-items: stretch;
      }

      .page-header button {
        width: 100%;
      }

      .filters-row {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-field {
        min-width: 0;
      }
    }
  `],
})
export class ProductListComponent implements OnInit, OnDestroy {
  displayedColumns = ['image', 'name', 'sku', 'price', 'category', 'brand', 'status', 'actions'];
  pageSize = 20;
  currency = '₽';
  destroy$ = new Subject<void>();

  // Form controls
  searchControl = new FormControl('');
  priceMinControl = new FormControl<number | null>(null);
  priceMaxControl = new FormControl<number | null>(null);

  // Observables from store
  products$!: Observable<Product[]>;
  loading$!: Observable<boolean>;
  totalPages$!: Observable<number>;
  totalCount$!: Observable<number>;
  currentPage$!: Observable<number>;

  constructor(
    private store: Store,
    private productService: ProductService,
    private router: Router
  ) {}

  ngOnInit(): void {
    // Initialize observables
    this.products$ = this.store.select(selectAllProducts);
    this.loading$ = this.store.select(selectProductsLoading);
    this.totalPages$ = this.store.select(selectTotalPages);
    this.totalCount$ = this.store.select(selectTotalCount);
    this.currentPage$ = this.store.select(selectCurrentPage);

    // Load initial data
    this.loadProducts();

    // Setup search debounce
    this.searchControl.valueChanges.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      takeUntil(this.destroy$)
    ).subscribe(() => {
      this.loadProducts({ page: 1 });
    });

    // Setup price filters
    this.priceMinControl.valueChanges.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      takeUntil(this.destroy$)
    ).subscribe(() => this.loadProducts({ page: 1 }));

    this.priceMaxControl.valueChanges.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      takeUntil(this.destroy$)
    ).subscribe(() => this.loadProducts({ page: 1 }));
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  loadProducts(params: Partial<ProductListParams> = {}): void {
    const search = this.searchControl.value || undefined;
    const priceMin = this.priceMinControl.value ?? undefined;
    const priceMax = this.priceMaxControl.value ?? undefined;

    this.store.dispatch(loadProducts({
      params: {
        page: params.page || 1,
        limit: this.pageSize,
        search,
        price_min: priceMin,
        price_max: priceMax,
      }
    }));
  }

  onPageChange(event: PageEvent): void {
    this.pageSize = event.pageSize;
    this.loadProducts({ page: event.pageIndex + 1 });
  }

  onSortChange(sort: Sort): void {
    // Note: Sorting is handled server-side via API params
    // You would need to add sort params to the API
  }

  clearFilters(): void {
    this.searchControl.setValue('');
    this.priceMinControl.setValue(null);
    this.priceMaxControl.setValue(null);
    this.loadProducts({ page: 1 });
  }

  get hasActiveFilters(): boolean {
    return !!(this.searchControl.value || 
              this.priceMinControl.value !== null || 
              this.priceMaxControl.value !== null);
  }

  getImageUrl(image: any): string {
    if (image.path) {
      return image.path;
    }
    if (image.url) {
      return image.url;
    }
    return '';
  }

  formatPrice(price: string): string {
    const num = parseFloat(price);
    return new Intl.NumberFormat('ru-RU', {
      style: 'currency',
      currency: 'RUB',
      minimumFractionDigits: 2,
    }).format(num);
  }

  navigateToDetail(product: Product): void {
    this.router.navigate(['/products', product.id]);
  }

  editProduct(product: Product): void {
    // Navigate to edit mode
    this.router.navigate(['/products', product.id, 'edit']);
  }

  deleteProduct(product: Product): void {
    if (confirm(`Are you sure you want to delete "${product.name}"?`)) {
      this.productService.deleteProduct(product.id).subscribe({
        next: () => {
          // Store will update via effect
        },
        error: (error: unknown) => {
          console.error('Delete failed', error);
        }
      });
    }
  }

  hasDiscount(product: Product): boolean {
    return !!product.discount && parseFloat(product.discount) > 0;
  }
}