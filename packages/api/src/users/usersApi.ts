
import type {
  CreateUserPayload,
  PaginatedResponse,
  UpdateUserPayload,
  User,
} from "@jm/types";

import { getApiClient } from "../client";

export async function getUsers(
  page = 1,
): Promise<PaginatedResponse<User>> {
  const response = await getApiClient().get<PaginatedResponse<User>>(
    "/api/users",
    { params: { page } },
  );

  return response.data;
}

export async function getUser(userId: number): Promise<User> {
  const response = await getApiClient().get<{ data: User }>(
    `/api/users/${userId}`,
  );

  return response.data.data;
}

export async function createUser(
  payload: CreateUserPayload,
): Promise<User> {
  const response = await getApiClient().post<{ data: User }>(
    "/api/users",
    payload,
  );

  return response.data.data;
}

export async function updateUser(
  userId: number,
  payload: UpdateUserPayload,
): Promise<User> {
  const response = await getApiClient().patch<{ data: User }>(
    `/api/users/${userId}`,
    payload,
  );

  return response.data.data;
}

export async function deleteUser(userId: number): Promise<void> {
  await getApiClient().delete(`/api/users/${userId}`);
}
