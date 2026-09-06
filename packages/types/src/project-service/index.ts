export interface ProjectService {
  id: number;
  service_id: string;
  name: string;
  description: string | null;
  quantity: string;
  unit_cost: string;
  total_cost: string;
}

export interface CreateProjectServicePayload {
  service_id: string;
  quantity?: number | string;
}

export interface UpdateProjectServicePayload {
  quantity: number | string;
}
