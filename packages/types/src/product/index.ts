export interface ProductCategory {
  id: number;
  name: string;
  slug: string;
  is_active: boolean;
}

export interface Product {
  id: string;
  name: string;
  description: string | null;
  price: string;
  stock: number;
  category: ProductCategory;
  primary_image_url: string | null;
  is_active: boolean;
  created_at: string | null;
  updated_at: string | null;
}

export interface ProductFilters {
  search?: string;
  category_id?: number;
  status?: "active" | "inactive";
  include_inactive?: boolean;
}

export interface CreateProductPayload {
  name: string;
  description?: string;
  price: string;
  stock: number;
  category_id: number;
  primary_image_url?: string;
}

export type UpdateProductPayload = Omit<CreateProductPayload, "stock">;

export interface AdjustProductStockPayload {
  quantity: number;
  type?: "adjustment" | "entry" | "exit" | "return";
  reason?: string;
  external_reference?: string;
}

export interface StockMovement {
  id: string;
  quantity_before: number;
  quantity_change: number;
  quantity_after: number;
  type: string;
  reason: string | null;
  external_reference: string | null;
  responsible_user_id: number | null;
  created_at: string | null;
}
