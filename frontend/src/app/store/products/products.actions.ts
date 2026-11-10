import { createAction, props } from '@ngrx/store';
import { Product, ProductListParams } from '../../models';

export const loadProducts = createAction(
  '[Products] Load Products',
  props<{ params: ProductListParams }>()
);

export const loadProductsSuccess = createAction(
  '[Products] Load Products Success',
  props<{ products: Product[]; meta: { page: number; limit: number; total: number; total_pages: number } }>()
);

export const loadProductsFailure = createAction(
  '[Products] Load Products Failure',
  props<{ error: string }>()
);

export const createProduct = createAction(
  '[Products] Create Product',
  props<{ product: Partial<Product> }>()
);

export const createProductSuccess = createAction(
  '[Products] Create Product Success',
  props<{ product: Product }>()
);

export const createProductFailure = createAction(
  '[Products] Create Product Failure',
  props<{ error: string }>()
);

export const updateProduct = createAction(
  '[Products] Update Product',
  props<{ id: string; product: Partial<Product> }>()
);

export const updateProductSuccess = createAction(
  '[Products] Update Product Success',
  props<{ product: Product }>()
);

export const updateProductFailure = createAction(
  '[Products] Update Product Failure',
  props<{ error: string }>()
);

export const deleteProduct = createAction(
  '[Products] Delete Product',
  props<{ id: string }>()
);

export const deleteProductSuccess = createAction(
  '[Products] Delete Product Success',
  props<{ id: string }>()
);

export const deleteProductFailure = createAction(
  '[Products] Delete Product Failure',
  props<{ error: string }>()
);

export const clearProducts = createAction('[Products] Clear Products');