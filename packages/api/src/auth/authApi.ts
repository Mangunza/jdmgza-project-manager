/**
 * Authentication API services.
 *
 * Centraliza as operações de autenticação
 * utilizadas pelo frontend e futuramente pelo mobile.
 */

import { getApiClient } from "../client";

import type {
  AuthResponse,
  ForgotPasswordPayload,
  ForgotPasswordResponse,
  LoginPayload,
  LogoutResponse,
  MeResponse,
  RegisterPayload,
  ResetPasswordPayload,
  ResetPasswordResponse,
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

export async function forgotPassword(
  payload: ForgotPasswordPayload,
): Promise<ForgotPasswordResponse> {
  const response =
    await getApiClient().post<ForgotPasswordResponse>(
      "/api/auth/forgot-password",
      payload,
    );

  return response.data;
}

export async function resetPassword(
  payload: ResetPasswordPayload,
): Promise<ResetPasswordResponse> {
  const response =
    await getApiClient().post<ResetPasswordResponse>(
      "/api/auth/reset-password",
      payload,
    );

  return response.data;
}
