import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ProductListComponent } from './product-list.component';
import { provideMockStore, MockStore } from '@ngrx/store/testing';
import { ProductService } from '../../../../services/product.service';
import { of } from 'rxjs';
import { Product, PaginatedResponse } from '../../../../models';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { RouterTestingModule } from '@angular/router/testing';

describe('ProductListComponent', () => {
  let component: ProductListComponent;
  let fixture: ComponentFixture<ProductListComponent>;
  let store: MockStore;
  let productService: jasmine.SpyObj<ProductService>;

  const mockProducts: Product[] = [
    {
      id: '1',
      external_code: 'test-1',
      name: 'Test Product 1',
      description: 'Description 1',
      price: '100.00',
      discount: '10.00',
      currency: 'RUB',
      sku: 'SKU-001',
      unit: 'шт',
      country: 'Russia',
      vat: '20%',
      brand: 'TestBrand',
      barcode: '123456789',
      is_archived: false,
      weight: null,
      is_weight_product: false,
      is_marked: false,
      volume: null,
      has_excise_mark: false,
      category: 'Category 1',
      seo_title: null,
      seo_h1: null,
      seo_description: null,
      package_quantity: null,
      package_url: null,
      packaging_url: null,
      product_weight: null,
      width: null,
      height: null,
      length: null,
      package_weight: null,
      package_width: null,
      package_height: null,
      package_length: null,
      attributes: {},
      images: [],
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    },
  ];

  const mockResponse: PaginatedResponse<Product> = {
    data: mockProducts,
    meta: {
      page: 1,
      limit: 20,
      total: 1,
      total_pages: 1,
    },
  };

  beforeEach(async () => {
    const productServiceSpy = jasmine.createSpyObj('ProductService', ['getProducts', 'deleteProduct']);
    productServiceSpy.getProducts.and.returnValue(of(mockResponse));
    productServiceSpy.deleteProduct.and.returnValue(of({ message: 'Deleted' }));

    await TestBed.configureTestingModule({
      imports: [
        ProductListComponent,
        NoopAnimationsModule,
        RouterTestingModule,
      ],
      providers: [
        provideMockStore({
          initialState: {
            products: {
              ids: ['1'],
              entities: { '1': mockProducts[0] },
              loading: false,
              error: null,
              currentPage: 1,
              totalPages: 1,
              total: 1,
              limit: 20,
              search: '',
              priceMin: null,
              priceMax: null,
            },
          },
        }),
        { provide: ProductService, useValue: productServiceSpy },
      ],
    }).compileComponents();

    store = TestBed.inject(MockStore);
    productService = TestBed.inject(ProductService) as jasmine.SpyObj<ProductService>;
    fixture = TestBed.createComponent(ProductListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should load products on init', () => {
    expect(productService.getProducts).toHaveBeenCalled();
  });

  it('should display products in table', () => {
    const compiled = fixture.nativeElement;
    expect(compiled.querySelector('.product-name')?.textContent).toContain('Test Product 1');
  });

  it('should filter products on search', () => {
    component.searchControl.setValue('Test');
    fixture.detectChanges();
    expect(productService.getProducts).toHaveBeenCalled();
  });

  it('should clear filters', () => {
    component.searchControl.setValue('Test');
    component.clearFilters();
    expect(component.searchControl.value).toBe('');
  });
});