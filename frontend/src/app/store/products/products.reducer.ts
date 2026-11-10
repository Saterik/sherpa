import { createReducer, on, Action } from '@ngrx/store';
import { EntityState, EntityAdapter, createEntityAdapter } from '@ngrx/entity';
import { Product } from '../../models';
import * as ProductsActions from './products.actions';

export interface ProductsState extends EntityState<Product> {
  loading: boolean;
  error: string | null;
  currentPage: number;
  totalPages: number;
  total: number;
  limit: number;
  search: string;
  priceMin: number | null;
  priceMax: number | null;
}

export const productsAdapter: EntityAdapter<Product> = createEntityAdapter<Product>({
  selectId: (product) => product.id,
  sortComparer: false,
});

export const initialState: ProductsState = productsAdapter.getInitialState({
  loading: false,
  error: null,
  currentPage: 1,
  totalPages: 0,
  total: 0,
  limit: 20,
  search: '',
  priceMin: null,
  priceMax: null,
});

export const productsReducer = createReducer(
  initialState,
  on(ProductsActions.loadProducts, (state, { params }) => ({
    ...state,
    loading: true,
    error: null,
    currentPage: params.page || 1,
    search: params.search || '',
    priceMin: params.price_min ?? null,
    priceMax: params.price_max ?? null,
  })),
  on(ProductsActions.loadProductsSuccess, (state, { products, meta }) =>
    productsAdapter.setAll(products, {
      ...state,
      loading: false,
      currentPage: meta.page,
      totalPages: meta.total_pages,
      total: meta.total,
      limit: meta.limit,
    })
  ),
  on(ProductsActions.loadProductsFailure, (state, { error }) => ({
    ...state,
    loading: false,
    error,
  })),
  on(ProductsActions.createProduct, (state) => ({
    ...state,
    loading: true,
    error: null,
  })),
  on(ProductsActions.createProductSuccess, (state, { product }) =>
    productsAdapter.addOne(product, {
      ...state,
      loading: false,
    })
  ),
  on(ProductsActions.createProductFailure, (state, { error }) => ({
    ...state,
    loading: false,
    error,
  })),
  on(ProductsActions.updateProduct, (state) => ({
    ...state,
    loading: true,
    error: null,
  })),
  on(ProductsActions.updateProductSuccess, (state, { product }) =>
    productsAdapter.updateOne({ id: product.id, changes: product }, {
      ...state,
      loading: false,
    })
  ),
  on(ProductsActions.updateProductFailure, (state, { error }) => ({
    ...state,
    loading: false,
    error,
  })),
  on(ProductsActions.deleteProduct, (state) => ({
    ...state,
    loading: true,
    error: null,
  })),
  on(ProductsActions.deleteProductSuccess, (state, { id }) =>
    productsAdapter.removeOne(id, {
      ...state,
      loading: false,
    })
  ),
  on(ProductsActions.deleteProductFailure, (state, { error }) => ({
    ...state,
    loading: false,
    error,
  })),
  on(ProductsActions.clearProducts, (state) =>
    productsAdapter.removeAll({ ...state, loading: false, error: null })
  )
);

export function reducer(state: ProductsState | undefined, action: Action) {
  return productsReducer(state, action);
}