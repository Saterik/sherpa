import { Injectable } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { Store } from '@ngrx/store';
import { of } from 'rxjs';
import { catchError, map, mergeMap, switchMap, withLatestFrom } from 'rxjs/operators';
import { ProductService } from '../../services/product.service';
import * as ProductsActions from './products.actions';

@Injectable()
export class ProductsEffects {
  constructor(
    private actions$: Actions,
    private productService: ProductService,
    private store: Store
  ) {}

  loadProducts$ = createEffect(() =>
    this.actions$.pipe(
      ofType(ProductsActions.loadProducts),
      withLatestFrom(this.store.select((state: any) => state.products)),
      switchMap(([action, state]) =>
        this.productService.getProducts(action.params).pipe(
          map((response) =>
            ProductsActions.loadProductsSuccess({
              products: response.data,
              meta: response.meta,
            })
          ),
          catchError((error) =>
            of(
              ProductsActions.loadProductsFailure({
                error: error.error?.error?.message || error.message || 'Failed to load products',
              })
            )
          )
        )
      )
    )
  );

  createProduct$ = createEffect(() =>
    this.actions$.pipe(
      ofType(ProductsActions.createProduct),
      mergeMap((action) =>
        this.productService.createProduct(action.product).pipe(
          map((response) =>
            ProductsActions.createProductSuccess({ product: response.data })
          ),
          catchError((error) =>
            of(
              ProductsActions.createProductFailure({
                error: error.error?.error?.message || error.message || 'Failed to create product',
              })
            )
          )
        )
      )
    )
  );

  updateProduct$ = createEffect(() =>
    this.actions$.pipe(
      ofType(ProductsActions.updateProduct),
      mergeMap((action) =>
        this.productService.updateProduct(action.id, action.product).pipe(
          map((response) =>
            ProductsActions.updateProductSuccess({ product: response.data })
          ),
          catchError((error) =>
            of(
              ProductsActions.updateProductFailure({
                error: error.error?.error?.message || error.message || 'Failed to update product',
              })
            )
          )
        )
      )
    )
  );

  deleteProduct$ = createEffect(() =>
    this.actions$.pipe(
      ofType(ProductsActions.deleteProduct),
      mergeMap((action) =>
        this.productService.deleteProduct(action.id).pipe(
          map(() => ProductsActions.deleteProductSuccess({ id: action.id })),
          catchError((error) =>
            of(
              ProductsActions.deleteProductFailure({
                error: error.error?.error?.message || error.message || 'Failed to delete product',
              })
            )
          )
        )
      )
    )
  );
}