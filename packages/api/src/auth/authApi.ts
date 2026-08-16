/**
 * Authentication API services.
 *
 * Será implementado quando conectarmos
 * o frontend ao Laravel Sanctum.
 */

import { getApiClient } from "../client";

import type {
  AuthResponse,
  LoginPayload,
  LogoutResponse,
  MeResponse,
  RegisterPayload,
} from "./types";

export async function register(
  payload: RegisterPayload,
): Promise<AuthResponse> {
  const response = await getApiClient().post<AuthResponse>(
    "/api/auth/register",
    payload,
  );

  localStorage.setItem("jm_auth_token", response.data.token);

  return response.data;
}

export async function login(
  payload: LoginPayload,
): Promise<AuthResponse> {
  const response = await getApiClient().post<AuthResponse>(
    "/api/auth/login",
    payload,
  );

  localStorage.setItem("jm_auth_token", response.data.token);

  return response.data;
}

export async function me(): Promise<MeResponse> {
  const response = await getApiClient().get<MeResponse>(
    "/api/auth/me",
  );

  return response.data;
}

export async function logout(): Promise<LogoutResponse> {
  const response = await getApiClient().post<LogoutResponse>(
    "/api/auth/logout",
  );

  localStorage.removeItem("jm_auth_token");

  return response.data;
}