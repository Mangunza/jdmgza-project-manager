import {
  createProjectService,
  deleteProjectService,
  getProjectServices,
  updateProjectService,
} from "@jm/api";
import type {
  CreateProjectServicePayload,
  ProjectService,
  UpdateProjectServicePayload,
} from "@jm/types";
import { useCallback, useEffect, useState } from "react";

export interface UseProjectServicesResult {
  services: ProjectService[];
  loading: boolean;
  error: Error | null;
  refresh: () => Promise<void>;
  add: (
    payload: CreateProjectServicePayload,
  ) => Promise<ProjectService>;
  update: (
    projectServiceId: number,
    payload: UpdateProjectServicePayload,
  ) => Promise<ProjectService>;
  remove: (projectServiceId: number) => Promise<void>;
}

export function useProjectServices(
  projectId: number,
): UseProjectServicesResult {
  const [services, setServices] = useState<ProjectService[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<Error | null>(null);

  const refresh = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await getProjectServices(projectId);

      setServices(response);
    } catch (cause) {
      const nextError =
        cause instanceof Error
          ? cause
          : new Error(
              "Não foi possível carregar os serviços do projeto.",
            );

      setError(nextError);
    } finally {
      setLoading(false);
    }
  }, [projectId]);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  const add = useCallback(
    async (
      payload: CreateProjectServicePayload,
    ): Promise<ProjectService> => {
      const projectService = await createProjectService(
        projectId,
        payload,
      );

      await refresh();

      return projectService;
    },
    [projectId, refresh],
  );

  const update = useCallback(
    async (
      projectServiceId: number,
      payload: UpdateProjectServicePayload,
    ): Promise<ProjectService> => {
      const projectService = await updateProjectService(
        projectId,
        projectServiceId,
        payload,
      );

      await refresh();

      return projectService;
    },
    [projectId, refresh],
  );

  const remove = useCallback(
    async (projectServiceId: number): Promise<void> => {
      await deleteProjectService(
        projectId,
        projectServiceId,
      );

      await refresh();
    },
    [projectId, refresh],
  );

  return {
    services,
    loading,
    error,
    refresh,
    add,
    update,
    remove,
  };
}
