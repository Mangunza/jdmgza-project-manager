export {
  createApiClient,
  getApiClient,
} from "./client";

export {
  getProducts,
} from "./products";

export {
  register,
  login,
  me,
  logout,
} from "./auth";

export type {
  AuthRole,
  AuthUser,
  RegisterPayload,
  LoginPayload,
  AuthResponse,
  MeResponse,
  LogoutResponse,
} from "./auth";
export {
  getProjects,
  getProject,
  createProject,
  updateProject,
  deleteProject,
} from "./projects";

export {
  getServices,
} from "./services";

export {
  getProjectServices,
  getProjectService,
  createProjectService,
  updateProjectService,
  deleteProjectService,
} from "./project-services";
