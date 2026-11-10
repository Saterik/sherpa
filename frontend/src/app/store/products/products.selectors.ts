import { createFeatureSelector, createSelector } from '@ngrx/store';
import { ProductsState, productsAdapter } from './products.reducer';

export const selectProductsState = createFeatureSelector<ProductsState>('products');

export const {
  selectAll: selectAllProducts,
  selectEntities: selectProductEntities,
  selectIds: selectProductIds,
  selectTotal: selectTotalProducts,
} = productsAdapter.getSelectors(selectProductsState);

export const selectProductsLoading = createSelector(
  selectProductsState,
  (state) => state.loading
);

export const selectProductsError = createSelector(
  selectProductsState,
  (state) => state.error
);

export const selectCurrentPage = createSelector(
  selectProductsState,
  (state) => state.currentPage
);

export const selectTotalPages = createSelector(
  selectProductsState,
  (state) => state.totalPages
);

export const selectTotalCount = createSelector(
  selectProductsState,
  (state) => state.total
);

export const selectLimit = createSelector(
  selectProductsState,
  (state) => state.limit
);

export const selectSearch = createSelector(
  selectProductsState,
  (state) => state.search
);

export const selectPriceMin = createSelector(
  selectProductsState,
  (state) => state.priceMin
);

export const selectPriceMax = createSelector(
  selectProductsState,
  (state) => state.priceMax
);

export const selectProductById = (id: string) => createSelector(
  selectProductEntities,
  (entities) => entities[id]
);