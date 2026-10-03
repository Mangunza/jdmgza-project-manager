export {
  createApiClient,
  getApiClient,
} from "./client";

export {
  ApiError,
} from "./errors";

export type {
  ApiErrorOptions,
  ApiValidationErrors,
} from "./errors";

export {
  getProducts,
} from "./products";

export {
  register,
  login,
  me,
  logout,
  forgotPassword,
  resetPassword,
} from "./auth";

export type {
  AuthRole,
  AuthUser,
  RegisterPayload,
  LoginPayload,
  AuthResponse,
  MeResponse,
  LogoutResponse,
  ForgotPasswordPayload,
  ForgotPasswordResponse,
  ResetPasswordPayload,
  ResetPasswordResponse,
} from "./auth";

export {
  getProjects,
  getProject,
  createProject,
  updateProject,
  deleteProject,
} from "./projects";

export {
  activateService,
  createService,
  deactivateService,
  getService,
  getServices,
  updateService,
} from "./services";

export {
  getProjectServices,
  getProjectService,
  createProjectService,
  updateProjectService,
  deleteProjectService,
} from "./project-services";
