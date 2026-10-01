import type {
  CreateServicePayload,
  Service,
  UpdateServicePayload,
} from "@jm/types";

import { getApiClient } from "../client";

export async function getServices(
  includeInactive = false,
): Promise<Service[]> {
  const response = await getApiClient().get<{
    data: Service[];
  }>("/api/services", {
    params: {
      include_inactive: includeInactive,
    },
  });

  return response.data.data;
}

export async function getService(serviceId: string): Promise<Service> {
  const response = await getApiClient().get<{
    data: Service;
  }>(`/api/services/${serviceId}`);

  return response.data.data;
}

export async function createService(
  payload: CreateServicePayload,
): Promise<Service> {
  const response = await getApiClient().post<{
    data: Service;
  }>("/api/services", payload);

  return response.data.data;
}

export async function updateService(
  serviceId: string,
  payload: UpdateServicePayload,
): Promise<Service> {
  const response = await getApiClient().patch<{
    data: Service;
  }>(`/api/services/${serviceId}`, payload);

  return response.data.data;
}

export async function activateService(serviceId: string): Promise<Service> {
  const response = await getApiClient().patch<{
    data: Service;
  }>(`/api/services/${serviceId}/activate`);

  return response.data.data;
}

export async function deactivateService(serviceId: string): Promise<Service> {
  const response = await getApiClient().patch<{
    data: Service;
  }>(`/api/services/${serviceId}/deactivate`);

  return response.data.data;
}
