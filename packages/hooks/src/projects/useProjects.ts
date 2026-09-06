import {
  createProject,
  deleteProject,
  getProjects,
  updateProject,
} from "@jm/api";
import type {
  CreateProjectPayload,
  Project,
  UpdateProjectPayload,
} from "@jm/types";
import { useCallback, useEffect, useState } from "react";

export interface UseProjectsResult {
  projects: Project[];
  loading: boolean;
  error: Error | null;
  page: number;
  lastPage: number;
  total: number;
  refresh: () => Promise<void>;
  setPage: (page: number) => void;
  create: (payload: CreateProjectPayload) => Promise<Project>;
  update: (
    projectId: number,
    payload: UpdateProjectPayload,
  ) => Promise<Project>;
  remove: (projectId: number) => Promise<void>;
}

export function useProjects(initialPage = 1): UseProjectsResult {
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<Error | null>(null);
  const [page, setPage] = useState(initialPage);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);

  const refresh = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await getProjects(page);

      setProjects(response.data);
      setLastPage(response.meta.last_page);
      setTotal(response.meta.total);
    } catch (cause) {
      const nextError =
        cause instanceof Error
          ? cause
          : new Error("Não foi possível carregar os projetos.");

      setError(nextError);
    } finally {
      setLoading(false);
    }
  }, [page]);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  const create = useCallback(
    async (payload: CreateProjectPayload): Promise<Project> => {
      const project = await createProject(payload);

      await refresh();

      return project;
    },
    [refresh],
  );

  const update = useCallback(
    async (
      projectId: number,
      payload: UpdateProjectPayload,
    ): Promise<Project> => {
      const project = await updateProject(projectId, payload);

      await refresh();

      return project;
    },
    [refresh],
  );

  const remove = useCallback(
    async (projectId: number): Promise<void> => {
      await deleteProject(projectId);

      await refresh();
    },
    [refresh],
  );

  return {
    projects,
    loading,
    error,
    page,
    lastPage,
    total,
    refresh,
    setPage,
    create,
    update,
    remove,
  };
}
