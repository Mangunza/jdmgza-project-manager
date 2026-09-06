import type {
  CreateProjectPayload,
  PaginatedResponse,
  Project,
  UpdateProjectPayload,
} from "@jm/types";

import { getApiClient } from "../client";

export async function getProjects(
  page = 1,
): Promise<PaginatedResponse<Project>> {
  const response = await getApiClient().get<PaginatedResponse<Project>>(
    "/api/projects",
    {
      params: {
        page,
      },
    },
  );

  return response.data;
}

export async function getProject(
  projectId: number,
): Promise<Project> {
  const response = await getApiClient().get<{ data: Project }>(
    `/api/projects/${projectId}`,
  );

  return response.data.data;
}

export async function createProject(
  payload: CreateProjectPayload,
): Promise<Project> {
  const response = await getApiClient().post<{ data: Project }>(
    "/api/projects",
    payload,
  );

  return response.data.data;
}

export async function updateProject(
  projectId: number,
  payload: UpdateProjectPayload,
): Promise<Project> {
  const response = await getApiClient().patch<{ data: Project }>(
    `/api/projects/${projectId}`,
    payload,
  );

  return response.data.data;
}

export async function deleteProject(
  projectId: number,
): Promise<void> {
  await getApiClient().delete(`/api/projects/${projectId}`);
}
