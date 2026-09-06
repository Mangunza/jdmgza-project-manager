import type {
  CreateProjectServicePayload,
  ProjectService,
  UpdateProjectServicePayload,
} from "@jm/types";

import { getApiClient } from "../client";

export async function getProjectServices(
  projectId: number,
): Promise<ProjectService[]> {
  const response = await getApiClient().get<{
    data: ProjectService[];
  }>(`/api/projects/${projectId}/services`);

  return response.data.data;
}

export async function getProjectService(
  projectId: number,
  projectServiceId: number,
): Promise<ProjectService> {
  const response = await getApiClient().get<{
    data: ProjectService;
  }>(
    `/api/projects/${projectId}/services/${projectServiceId}`,
  );

  return response.data.data;
}

export async function createProjectService(
  projectId: number,
  payload: CreateProjectServicePayload,
): Promise<ProjectService> {
  const response = await getApiClient().post<{
    data: ProjectService;
  }>(
    `/api/projects/${projectId}/services`,
    payload,
  );

  return response.data.data;
}

export async function updateProjectService(
  projectId: number,
  projectServiceId: number,
  payload: UpdateProjectServicePayload,
): Promise<ProjectService> {
  const response = await getApiClient().patch<{
    data: ProjectService;
  }>(
    `/api/projects/${projectId}/services/${projectServiceId}`,
    payload,
  );

  return response.data.data;
}

export async function deleteProjectService(
  projectId: number,
  projectServiceId: number,
): Promise<void> {
  await getApiClient().delete(
    `/api/projects/${projectId}/services/${projectServiceId}`,
  );
}
