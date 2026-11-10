import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { Store, select } from '@ngrx/store';
import { Observable, Subject, takeUntil, switchMap, map } from 'rxjs';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatChipsModule } from '@angular/material/chips';
import { MatDividerModule } from '@angular/material/divider';
import { MatListModule } from '@angular/material/list';
import { MatTabsModule } from '@angular/material/tabs';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatBadgeModule } from '@angular/material/badge';
import { Product } from '@app/models';
import { selectProductById } from '@app/store/products/products.selectors';
import { ProductService } from '@app/services/product.service';
import { loadProducts } from '@app/store/products/products.actions';

@Component({
  selector: 'app-product-detail',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatChipsModule,
    MatDividerModule,
    MatListModule,
    MatTabsModule,
    MatTooltipModule,
    MatBadgeModule,
    RouterModule,
  ],
  template: `
    @if (product$ | async; as product) {
      <div class="product-detail-container">
        <!-- Header -->
        <div class="detail-header">
          <div class="header-content">
            <button mat-icon-button (click)="goBack()" aria-label="Go back">
              <mat-icon>arrow_back</mat-icon>
            </button>
            <div>
              <h1>{{ product.name }}</h1>
              <p class="product-code">{{ product.external_code }}</p>
            </div>
          </div>
          
          <div class="header-actions">
            <mat-chip [color]="product.is_archived ? 'warn' : 'primary'">
              {{ product.is_archived ? 'Archived' : 'Active' }}
            </mat-chip>
            <button mat-stroked-button (click)="editProduct(product)">
              <mat-icon>edit</mat-icon>
              Edit
            </button>
            <button mat-stroked-button color="warn" (click)="deleteProduct(product)">
              <mat-icon>delete</mat-icon>
              Delete
            </button>
          </div>
        </div>

        <mat-divider></mat-divider>

        <!-- Main Content -->
        <div class="detail-content">
          <!-- Left Column - Images & Basic Info -->
          <div class="left-column">
            <!-- Images -->
            <mat-card class="images-card">
              <mat-card-header>
                <mat-card-title>Images</mat-card-title>
              </mat-card-header>
              <mat-card-content>
                @if (product.images && product.images.length > 0) {
                  <div class="images-grid">
                    @for (image of product.images; track image.id) {
                      <div class="image-item" [class.main]="image.is_main">
                        @if (image.path) {
                          <img [src]="image.path" [alt]="product.name" (error)="onImageError($event, image)" loading="lazy">
                        } @else if (image.url) {
                          <img [src]="image.url" [alt]="product.name" (error)="onImageError($event, image)" loading="lazy">
                        } @else {
                          <div class="image-placeholder">
                            <mat-icon>image_not_supported</mat-icon>
                          </div>
                        }
                        @if (image.is_main) {
                          <mat-chip class="main-badge" color="primary">Main</mat-chip>
                        }
                        @if (image.download_status === 'failed') {
                          <mat-chip class="error-badge" color="warn">Failed</mat-chip>
                        }
                      </div>
                    }
                  </div>
                } @else {
                  <p class="no-images">No images available</p>
                }
              </mat-card-content>
            </mat-card>

            <!-- Basic Info -->
            <mat-card class="info-card">
              <mat-card-header>
                <mat-card-title>Basic Information</mat-card-title>
              </mat-card-header>
              <mat-card-content>
                <dl class="info-list">
                  <div class="info-row">
                    <dt>SKU / Code</dt>
                    <dd>{{ product.sku || '-' }}</dd>
                  </div>
                  <div class="info-row">
                    <dt>Price</dt>
                    <dd class="price">{{ formatPrice(product.price) }}</dd>
                  </div>
                  <div class="info-row">
                    <dt>Currency</dt>
                    <dd>{{ product.currency || 'RUB' }}</dd>
                  </div>
                  @if (product.discount && hasDiscount(product)) {
                    <div class="info-row">
                      <dt>Discount</dt>
                      <dd><mat-chip color="warn">{{ product.discount }}%</mat-chip></dd>
                    </div>
                  }
                  <div class="info-row">
                    <dt>Unit</dt>
                    <dd>{{ product.unit || '-' }}</dd>
                  </div>
                  <div class="info-row">
                    <dt>Barcode</dt>
                    <dd>{{ product.barcode || '-' }}</dd>
                  </div>
                  <div class="info-row">
                    <dt>Country</dt>
                    <dd>{{ product.country || '-' }}</dd>
                  </div>
                  <div class="info-row">
                    <dt>VAT</dt>
                    <dd>{{ product.vat || '-' }}</dd>
                  </div>
                  <div class="info-row">
                    <dt>Brand</dt>
                    <dd>{{ product.brand || '-' }}</dd>
                  </div>
                  <div class="info-row">
                    <dt>Category</dt>
                    <dd>{{ product.category || '-' }}</dd>
                  </div>
                  <div class="info-row">
                    <dt>Weight</dt>
                    <dd>{{ product.weight ? product.weight + ' kg' : '-' }}</dd>
                  </div>
                  <div class="info-row">
                    <dt>Volume</dt>
                    <dd>{{ product.volume ? product.volume + ' m³' : '-' }}</dd>
                  </div>
                  <div class="info-row">
                    <dt>Created</dt>
                    <dd>{{ formatDate(product.created_at) }}</dd>
                  </div>
                  <div class="info-row">
                    <dt>Updated</dt>
                    <dd>{{ formatDate(product.updated_at) }}</dd>
                  </div>
                </dl>
              </mat-card-content>
            </mat-card>
          </div>

          <!-- Right Column - Description, Attributes, SEO -->
          <div class="right-column">
            <!-- Description -->
            @if (product.description) {
              <mat-card class="description-card">
                <mat-card-header>
                  <mat-card-title>Description</mat-card-title>
                </mat-card-header>
                <mat-card-content>
                  <div class="description-content" [innerHTML]="product.description"></div>
                </mat-card-content>
              </mat-card>
            }

            <!-- Attributes -->
            @if (product.attributes && hasAttributes(product)) {
              <mat-card class="attributes-card">
                <mat-card-header>
                  <mat-card-title>Additional Attributes</mat-card-title>
                </mat-card-header>
                <mat-card-content>
                  <dl class="attributes-list">
                    @for (key of attributeKeys(product.attributes); track key) {
                      <div class="attribute-row">
                        <dt>{{ key }}</dt>
                        <dd>{{ product.attributes[key] }}</dd>
                      </div>
                    }
                  </dl>
                </mat-card-content>
              </mat-card>
            }

            <!-- SEO -->
            @if (product.seo_title || product.seo_h1 || product.seo_description) {
              <mat-card class="seo-card">
                <mat-card-header>
                  <mat-card-title>SEO Information</mat-card-title>
                </mat-card-header>
                <mat-card-content>
                  <dl class="seo-list">
                    @if (product.seo_title) {
                      <div class="seo-row">
                        <dt>SEO Title</dt>
                        <dd>{{ product.seo_title }}</dd>
                      </div>
                    }
                    @if (product.seo_h1) {
                      <div class="seo-row">
                        <dt>SEO H1</dt>
                        <dd>{{ product.seo_h1 }}</dd>
                      </div>
                    }
                    @if (product.seo_description) {
                      <div class="seo-row">
                        <dt>SEO Description</dt>
                        <dd>{{ product.seo_description }}</dd>
                      </div>
                    }
                  </dl>
                </mat-card-content>
              </mat-card>
            }

            <!-- Dimensions -->
            @if (hasDimensions(product)) {
              <mat-card class="dimensions-card">
                <mat-card-header>
                  <mat-card-title>Dimensions & Weight</mat-card-title>
                </mat-card-header>
                <mat-card-content>
                  <div class="dimensions-grid">
                    @if (product.width) {
                      <div class="dimension-item">
                        <span class="dim-label">Width</span>
                        <span class="dim-value">{{ product.width }} mm</span>
                      </div>
                    }
                    @if (product.height) {
                      <div class="dimension-item">
                        <span class="dim-label">Height</span>
                        <span class="dim-value">{{ product.height }} mm</span>
                      </div>
                    }
                    @if (product.length) {
                      <div class="dimension-item">
                        <span class="dim-label">Length</span>
                        <span class="dim-value">{{ product.length }} mm</span>
                      </div>
                    }
                    @if (product.product_weight) {
                      <div class="dimension-item">
                        <span class="dim-label">Product Weight</span>
                        <span class="dim-value">{{ product.product_weight }} g</span>
                      </div>
                    }
                    @if (product.package_width) {
                      <div class="dimension-item">
                        <span class="dim-label">Package Width</span>
                        <span class="dim-value">{{ product.package_width }} mm</span>
                      </div>
                    }
                    @if (product.package_height) {
                      <div class="dimension-item">
                        <span class="dim-label">Package Height</span>
                        <span class="dim-value">{{ product.package_height }} mm</span>
                      </div>
                    }
                    @if (product.package_length) {
                      <div class="dimension-item">
                        <span class="dim-label">Package Length</span>
                        <span class="dim-value">{{ product.package_length }} mm</span>
                      </div>
                    }
                    @if (product.package_weight) {
                      <div class="dimension-item">
                        <span class="dim-label">Package Weight</span>
                        <span class="dim-value">{{ product.package_weight }} g</span>
                      </div>
                    }
                    @if (product.package_quantity) {
                      <div class="dimension-item">
                        <span class="dim-label">Qty in Package</span>
                        <span class="dim-value">{{ product.package_quantity }}</span>
                      </div>
                    }
                  </div>
                </mat-card-content>
              </mat-card>
            }

            <!-- Flags -->
            <mat-card class="flags-card">
              <mat-card-header>
                <mat-card-title>Product Flags</mat-card-title>
              </mat-card-header>
              <mat-card-content>
                <div class="flags-grid">
                  <mat-chip [color]="product.is_archived ? 'warn' : 'primary'">
                    {{ product.is_archived ? 'Archived' : 'Active' }}
                  </mat-chip>
                  <mat-chip [color]="product.is_weight_product ? 'primary' : ''">
                    Weight Product
                  </mat-chip>
                  <mat-chip [color]="product.is_marked ? 'primary' : ''">
                    Marked
                  </mat-chip>
                  <mat-chip [color]="product.has_excise_mark ? 'primary' : ''">
                    Excise Mark
                  </mat-chip>
                </div>
              </mat-card-content>
            </mat-card>
          </div>
        </div>
      </div>
    } @else {
      <div class="loading-container">
        <mat-spinner diameter="50"></mat-spinner>
        <p>Loading product...</p>
      </div>
    }
  `,
  styles: [`
    .product-detail-container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .detail-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 16px;
    }

    .header-content {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .header-content h1 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 500;
    }

    .product-code {
      margin: 4px 0 0;
      font-family: monospace;
      color: rgba(0,0,0,0.6);
      font-size: 0.875rem;
    }

    .header-actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .detail-content {
      display: grid;
      grid-template-columns: 350px 1fr;
      gap: 24px;
    }

    .left-column {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .images-card {
      overflow: hidden;
    }

    .images-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 12px;
    }

    .image-item {
      position: relative;
      aspect-ratio: 1;
      border-radius: 8px;
      overflow: hidden;
      background: #f5f5f5;
      border: 1px solid rgba(0,0,0,0.1);
    }

    .image-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.2s;
    }

    .image-item:hover img {
      transform: scale(1.05);
    }

    .image-placeholder {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 100%;
      color: rgba(0,0,0,0.3);
    }

    .main-badge {
      position: absolute;
      top: 8px;
      left: 8px;
      font-size: 0.65rem;
      height: 20px;
    }

    .error-badge {
      position: absolute;
      top: 8px;
      right: 8px;
      font-size: 0.65rem;
      height: 20px;
    }

    .no-images {
      text-align: center;
      color: rgba(0,0,0,0.4);
      padding: 32px;
    }

    .info-card {
      overflow: hidden;
    }

    .info-list {
      margin: 0;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid rgba(0,0,0,0.06);
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-row dt {
      margin: 0;
      font-weight: 500;
      color: rgba(0,0,0,0.6);
      font-size: 0.875rem;
    }

    .info-row dd {
      margin: 0;
      text-align: right;
      max-width: 60%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .price {
      font-weight: 600;
      font-size: 1rem;
    }

    .right-column {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .description-content {
      line-height: 1.7;
      white-space: pre-wrap;
    }

    .attributes-list {
      margin: 0;
    }

    .attribute-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid rgba(0,0,0,0.06);
    }

    .attribute-row:last-child {
      border-bottom: none;
    }

    .attribute-row dt {
      margin: 0;
      font-weight: 500;
      color: rgba(0,0,0,0.6);
    }

    .attribute-row dd {
      margin: 0;
      text-align: right;
      max-width: 60%;
    }

    .seo-list {
      margin: 0;
    }

    .seo-row {
      margin-bottom: 16px;
    }

    .seo-row:last-child {
      margin-bottom: 0;
    }

    .seo-row dt {
      margin: 0 0 4px;
      font-weight: 500;
      color: rgba(0,0,0,0.6);
      font-size: 0.875rem;
    }

    .seo-row dd {
      margin: 0;
      padding: 12px;
      background: #f5f5f5;
      border-radius: 4px;
      font-family: monospace;
      font-size: 0.875rem;
      white-space: pre-wrap;
    }

    .dimensions-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }

    .dimension-item {
      padding: 12px;
      background: #f5f5f5;
      border-radius: 8px;
    }

    .dim-label {
      display: block;
      font-size: 0.75rem;
      color: rgba(0,0,0,0.5);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .dim-value {
      font-weight: 500;
    }

    .flags-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .loading-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 400px;
      gap: 16px;
      color: rgba(0,0,0,0.6);
    }

    @media (max-width: 1024px) {
      .detail-content {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .detail-header {
        flex-direction: column;
        align-items: stretch;
      }

      .header-actions {
        justify-content: flex-end;
      }

      .images-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
      }

      .dimensions-grid {
        grid-template-columns: 1fr;
      }
    }
  `],
})
export class ProductDetailComponent implements OnInit, OnDestroy {
  product$!: Observable<Product | undefined>;
  private destroy$ = new Subject<void>();

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private store: Store,
    private productService: ProductService
  ) {}

  ngOnInit(): void {
    this.product$ = this.route.paramMap.pipe(
      map(params => params.get('id')),
      switchMap(id => {
        if (id) {
          return this.store.select(selectProductById(id));
        }
        return [undefined];
      }),
      takeUntil(this.destroy$)
    );
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  goBack(): void {
    this.router.navigate(['/products']);
  }

  editProduct(product: Product): void {
    this.router.navigate(['/products', product.id, 'edit']);
  }

  deleteProduct(product: Product): void {
    if (confirm(`Are you sure you want to delete "${product.name}"?`)) {
      this.productService.deleteProduct(product.id).subscribe({
        next: () => {
          this.router.navigate(['/products']);
        },
        error: (error: unknown) => {
          console.error('Delete failed', error);
        }
      });
    }
  }

  formatPrice(price: string): string {
    const num = parseFloat(price);
    return new Intl.NumberFormat('ru-RU', {
      style: 'currency',
      currency: 'RUB',
      minimumFractionDigits: 2,
    }).format(num);
  }

  formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleString('ru-RU', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  }

  attributeKeys(attributes: Record<string, string>): string[] {
    return Object.keys(attributes).sort();
  }

  hasDimensions(product: Product): boolean {
    return !!(
      product.width || product.height || product.length ||
      product.product_weight || product.package_width ||
      product.package_height || product.package_length ||
      product.package_weight || product.package_quantity
    );
  }

  onImageError(event: Event, image: any): void {
    // Image failed to load, could set a flag to show placeholder
    console.warn('Image failed to load:', image.url || image.path);
  }

  hasDiscount(product: Product): boolean {
    return !!product.discount && parseFloat(product.discount) > 0;
  }

  hasAttributes(product: Product): boolean {
    return !!product.attributes && Object.keys(product.attributes).length > 0;
  }
}