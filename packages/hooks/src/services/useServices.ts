import { getServices } from "@jm/api";
import type { Service } from "@jm/types";
import { useCallback, useEffect, useState } from "react";

export interface UseServicesResult {
  services: Service[];
  loading: boolean;
  error: Error | null;
  refresh: () => Promise<void>;
}

export function useServices(): UseServicesResult {
  const [services, setServices] = useState<Service[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<Error | null>(null);

  const refresh = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await getServices();

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
  }, []);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  return {
    services,
    loading,
    error,
    refresh,
  };
}
