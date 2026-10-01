import {
  activateService,
  createService,
  deactivateService,
  getServices,
  updateService,
} from "@jm/api";
import type {
  CreateServicePayload,
  Service,
  UpdateServicePayload,
} from "@jm/types";
import { useCallback, useEffect, useState } from "react";

export interface UseServicesResult {
  services: Service[];
  loading: boolean;
  error: Error | null;
  refresh: () => Promise<void>;
  create: (payload: CreateServicePayload) => Promise<Service>;
  update: (
    serviceId: string,
    payload: UpdateServicePayload,
  ) => Promise<Service>;
  activate: (serviceId: string) => Promise<Service>;
  deactivate: (serviceId: string) => Promise<Service>;
}

export function useServices(
  includeInactive = false,
): UseServicesResult {
  const [services, setServices] = useState<Service[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<Error | null>(null);

  const refresh = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await getServices(includeInactive);

      setServices(response);
    } catch (cause) {
      const nextError =
        cause instanceof Error
          ? cause
          : new Error("Não foi possível carregar os serviços.");

      setError(nextError);
    } finally {
      setLoading(false);
    }
  }, [includeInactive]);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  const create = useCallback(
    async (payload: CreateServicePayload): Promise<Service> => {
      const service = await createService(payload);

      await refresh();

      return service;
    },
    [refresh],
  );

  const update = useCallback(
    async (
      serviceId: string,
      payload: UpdateServicePayload,
    ): Promise<Service> => {
      const service = await updateService(serviceId, payload);

      await refresh();

      return service;
    },
    [refresh],
  );

  const activate = useCallback(
    async (serviceId: string): Promise<Service> => {
      const service = await activateService(serviceId);

      await refresh();

      return service;
    },
    [refresh],
  );

  const deactivate = useCallback(
    async (serviceId: string): Promise<Service> => {
      const service = await deactivateService(serviceId);

      await refresh();

      return service;
    },
    [refresh],
  );

  return {
    services,
    loading,
    error,
    refresh,
    create,
    update,
    activate,
    deactivate,
  };
}
