import type {
  CreateProductPayload,
  AdjustProductStockPayload,
  Product,
  ProductCategory,
  ProductFilters,
  UpdateProductPayload,
  StockMovement,
} from "@jm/types";

import { getApiClient } from "../client";

export async function getProducts(filters: ProductFilters = {}): Promise<Product[]> {
  const response = await getApiClient().get<{ data: Product[] }>("/api/products", { params: filters });
  return response.data.data;
}

export async function getProduct(productId: string): Promise<Product> {
  const response = await getApiClient().get<{ data: Product }>(`/api/products/${productId}`);
  return response.data.data;
}

export async function getProductCategories(): Promise<ProductCategory[]> {
  const response = await getApiClient().get<{ data: ProductCategory[] }>("/api/categories");
  return response.data.data;
}

export async function createProduct(payload: CreateProductPayload): Promise<Product> {
  const response = await getApiClient().post<{ data: Product }>("/api/products", payload);
  return response.data.data;
}

export async function updateProduct(productId: string, payload: UpdateProductPayload): Promise<Product> {
  const response = await getApiClient().patch<{ data: Product }>(`/api/products/${productId}`, payload);
  return response.data.data;
}

export async function adjustProductStock(productId: string, payload: AdjustProductStockPayload): Promise<Product> {
  const response = await getApiClient().patch<{ data: Product }>(`/api/products/${productId}/stock`, payload);
  return response.data.data;
}

export async function getStockMovements(productId: string): Promise<StockMovement[]> {
  const response = await getApiClient().get<{ data: StockMovement[] }>(`/api/products/${productId}/stock-movements`);
  return response.data.data;
}

export async function setProductActive(productId: string, isActive: boolean): Promise<Product> {
  const action = isActive ? "activate" : "deactivate";
  const response = await getApiClient().patch<{ data: Product }>(`/api/products/${productId}/${action}`);
  return response.data.data;
}
