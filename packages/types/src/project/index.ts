export type ProjectStatus =
  | "draft"
  | "planning"
  | "quoted"
  | "approved"
  | "in_progress"
  | "completed"
  | "cancelled";

export interface ProjectCategory {
  id: number;
  name: string;
  slug: string;
}

export interface Project {
  id: number;
  name: string;
  description: string | null;
  category?: ProjectCategory;
  total_budget: string;
  total_cost: string;
  delivery_date: string | null;
  status: ProjectStatus | null;
  created_at: string | null;
  updated_at: string | null;
}

export interface CreateProjectPayload {
  category_id: number;
  name: string;
  description?: string | null;
  total_budget: number | string;
  delivery_date?: string | null;
}

export interface UpdateProjectPayload {
  category_id?: number;
  name?: string;
  description?: string | null;
  total_budget?: number | string;
  delivery_date?: string | null;
}
