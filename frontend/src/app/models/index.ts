export interface Product {
  id: string;
  external_code: string;
  name: string;
  description: string | null;
  price: string;
  discount: string | null;
  currency: string | null;
  sku: string | null;
  unit: string | null;
  country: string | null;
  vat: string | null;
  brand: string | null;
  barcode: string | null;
  is_archived: boolean;
  weight: string | null;
  is_weight_product: boolean;
  is_marked: boolean;
  volume: string | null;
  has_excise_mark: boolean;
  category: string | null;
  seo_title: string | null;
  seo_h1: string | null;
  seo_description: string | null;
  package_quantity: number | null;
  package_url: string | null;
  packaging_url: string | null;
  product_weight: number | null;
  width: number | null;
  height: number | null;
  length: number | null;
  package_weight: number | null;
  package_width: number | null;
  package_height: number | null;
  package_length: number | null;
  attributes: Record<string, string>;
  images: ProductImage[];
  created_at: string;
  updated_at: string;
}

export interface ProductImage {
  id: string;
  url: string;
  path: string | null;
  filename: string | null;
  size: number | null;
  mime_type: string | null;
  is_main: boolean;
  sort_order: number;
  download_status: string | null;
  download_error: string | null;
  created_at: string;
  downloaded_at: string | null;
}

export interface ProductAttribute {
  id: string;
  key: string;
  value: string | null;
  created_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    page: number;
    limit: number;
    total: number;
    total_pages: number;
  };
}

export interface ProductListParams {
  page?: number;
  limit?: number;
  search?: string;
  price_min?: number;
  price_max?: number;
}

export interface ImportJob {
  job_id: string;
  status: 'queued' | 'processing' | 'completed' | 'failed';
  progress: number;
  message: string;
}

export interface ImportReport {
  job_id: string;
  errors: ImportError[];
  stats: {
    total: number;
    created: number;
    updated: number;
    failed: number;
  };
}

export interface ImportError {
  row: number;
  external_code: string;
  error: string;
  errors?: string[];
}

export interface AuthResponse {
  access_token: string;
  refresh_token: string;
  token_type: string;
  expires_in: number;
  user: User;
}

export interface User {
  id: string;
  email: string;
  name: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  email: string;
  password: string;
  name: string;
}

export interface HealthCheck {
  status: 'healthy' | 'unhealthy';
  checks: {
    database: boolean;
    timestamp: string;
  };
}