import axios, {
  type AxiosInstance,
} from "axios";

export interface ApiClientConfig {
  baseURL: string;
}

let apiClient: AxiosInstance | null = null;

export function createApiClient(
  config: ApiClientConfig,
): AxiosInstance {
  apiClient = axios.create({
    baseURL: config.baseURL,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
  });

  apiClient.interceptors.request.use((request) => {
    const token = localStorage.getItem("jm_auth_token");

    if (token) {
      request.headers.Authorization = `Bearer ${token}`;
    }

    return request;
  });

  return apiClient;
}

export function getApiClient(): AxiosInstance {
  if (!apiClient) {
    throw new Error(
      "API client não foi configurado. Execute createApiClient() primeiro.",
    );
  }

  return apiClient;
}
