import { createApiClient } from "@jm/api";

const baseURL = import.meta.env.VITE_API_URL;

if (!baseURL) {
  throw new Error("VITE_API_URL não está definida.");
}

createApiClient({
  baseURL,
});
