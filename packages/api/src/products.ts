import type { Product } from "@jm/types";
import { apiClient } from "./client";

interface ProductsResponse {
  products: Product[];
  total: number;
  skip: number;
  limit: number;
}

/**
 * Busca todos os produtos da API.
 *
 * Atualmente utiliza DummyJSON para testes.
 *
 * FUTURO:
 * substituir o endpoint pela API real do backend.
 */
export async function getProducts(): Promise<Product[]> {
  const response = await apiClient.get<ProductsResponse>("/products");

  return response.data.products;
}
