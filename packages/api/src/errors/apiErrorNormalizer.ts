import {
  isAxiosError,
} from "axios";

import {
  ApiError,
} from "./ApiError";

interface ApiErrorResponse {
  message?: unknown;
  errors?: unknown;
  code?: unknown;
}

function getFallbackMessage(status?: number): string {
  switch (status) {
    case 400:
      return "Não foi possível processar a solicitação.";

    case 401:
      return "A sua sessão expirou ou não é válida.";

    case 403:
      return "Não tem permissão para realizar esta ação.";

    case 404:
      return "O recurso solicitado não foi encontrado.";

    case 422:
      return "Os dados enviados são inválidos.";

    case 429:
      return "Demasiadas solicitações. Tente novamente mais tarde.";

    default:
      if (status !== undefined && status >= 500) {
        return "Ocorreu um erro no servidor. Tente novamente mais tarde.";
      }

      return "Ocorreu um erro ao comunicar com o servidor.";
  }
}

function normalizeValidationErrors(
  errors: unknown,
): Record<string, string[]> {
  if (!errors || typeof errors !== "object" || Array.isArray(errors)) {
    return {};
  }

  const normalized: Record<string, string[]> = {};

  for (const [field, messages] of Object.entries(errors)) {
    if (Array.isArray(messages)) {
      const strings = messages.filter(
        (message): message is string =>
          typeof message === "string",
      );

      if (strings.length > 0) {
        normalized[field] = strings;
      }
    }
  }

  return normalized;
}

export function normalizeApiError(
  error: unknown,
): ApiError {
  if (!isAxiosError(error)) {
    if (error instanceof ApiError) {
      return error;
    }

    if (error instanceof Error) {
      return new ApiError(error.message);
    }

    return new ApiError(
      "Ocorreu um erro inesperado.",
    );
  }

  const status = error.response?.status;
  const data = error.response?.data as
    | ApiErrorResponse
    | undefined;

  const message =
    typeof data?.message === "string" && data.message.trim()
      ? data.message
      : getFallbackMessage(status);

  const validationErrors = normalizeValidationErrors(
    data?.errors,
  );

  const code =
    typeof data?.code === "string"
      ? data.code
      : undefined;

  return new ApiError(message, {
    status,
    validationErrors,
    code,
  });
}
