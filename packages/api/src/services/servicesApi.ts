import type { Service } from "@jm/types";

import { getApiClient } from "../client";

export async function getServices(): Promise<Service[]> {
  const response = await getApiClient().get<{
    data: Service[];
  }>("/api/services");

  return response.data.data;
}
