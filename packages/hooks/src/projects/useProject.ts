import { getProject, updateProject } from "@jm/api";
import type { Project, UpdateProjectPayload } from "@jm/types";
import { useCallback, useEffect, useState } from "react";

export interface UseProjectResult {
  project: Project | null;
  loading: boolean;
  error: Error | null;
  refresh: () => Promise<void>;
  update: (payload: UpdateProjectPayload) => Promise<Project>;
}

export function useProject(projectId: number): UseProjectResult {
  const [project, setProject] = useState<Project | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<Error | null>(null);

  const refresh = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await getProject(projectId);

      setProject(response);
    } catch (cause) {
      const nextError =
        cause instanceof Error
          ? cause
          : new Error("Não foi possível carregar o projeto.");

      setError(nextError);
    } finally {
      setLoading(false);
    }
  }, [projectId]);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  const update = useCallback(
    async (payload: UpdateProjectPayload): Promise<Project> => {
      const updatedProject = await updateProject(projectId, payload);

      setProject(updatedProject);

      return updatedProject;
    },
    [projectId],
  );

  return {
    project,
    loading,
    error,
    refresh,
    update,
  };
}
