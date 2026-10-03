import { getService, updateService } from "@jm/api";
import type {
  Service,
  UpdateServicePayload,
} from "@jm/types";
import { useCallback, useEffect, useState } from "react";

export interface UseServiceResult {
  service: Service | null;
  loading: boolean;
  error: Error | null;
  refresh: () => Promise<void>;
  update: (payload: UpdateServicePayload) => Promise<Service>;
}

export function useService(serviceId: string): UseServiceResult {
  const [service, setService] = useState<Service | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<Error | null>(null);

  const refresh = useCallback(async () => {
    if (!serviceId) {
      setService(null);
      setError(new Error("ID do serviço não informado."));
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await getService(serviceId);

      setService(response);
    } catch (cause) {
      const nextError =
        cause instanceof Error
          ? cause
          : new Error("Não foi possível carregar o serviço.");

      setError(nextError);
    } finally {
      setLoading(false);
    }
  }, [serviceId]);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  const update = useCallback(
    async (payload: UpdateServicePayload): Promise<Service> => {
      if (!serviceId) {
        throw new Error("ID do serviço não informado.");
      }

      setError(null);

      try {
        const response = await updateService(serviceId, payload);

        setService(response);

        return response;
      } catch (cause) {
        const nextError =
          cause instanceof Error
            ? cause
            : new Error("Não foi possível atualizar o serviço.");

        setError(nextError);

        throw nextError;
      }
    },
    [serviceId],
  );

  return {
    service,
    loading,
    error,
    refresh,
    update,
  };
}
