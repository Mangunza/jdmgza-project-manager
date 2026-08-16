import type { Product } from "@jm/types";

import { getApiClient } from "../client";

interface ProductsResponse {
  products: Product[];
  total: number;
  skip: number;
  limit: number;
}

/**
 * Busca todos os produtos.
 */
export async function getProducts(): Promise<Product[]> {
  const apiClient = getApiClient();

  const response = await apiClient.get<ProductsResponse>("/products");

  return response.data.products;
}
