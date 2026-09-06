export interface Service {
  id: string;
  name: string;
  description: string | null;
  default_cost: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface CreateServicePayload {
  name: string;
  description?: string | null;
  default_cost: number | string;
}

export interface UpdateServicePayload {
  name?: string;
  description?: string | null;
  default_cost?: number | string;
}
