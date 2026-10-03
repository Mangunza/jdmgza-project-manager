export type ApiValidationErrors = Record<string, string[]>;

export interface ApiErrorOptions {
  status?: number;
  validationErrors?: ApiValidationErrors;
  code?: string;
}

export class ApiError extends Error {
  readonly status: number | undefined;
  readonly validationErrors: ApiValidationErrors;
  readonly code: string | undefined;

  constructor(
    message: string,
    options: ApiErrorOptions = {},
  ) {
    super(message);

    this.name = "ApiError";
    this.status = options.status;
    this.validationErrors = options.validationErrors ?? {};
    this.code = options.code;

    Object.setPrototypeOf(this, new.target.prototype);
  }

  get isUnauthorized(): boolean {
    return this.status === 401;
  }

  get isForbidden(): boolean {
    return this.status === 403;
  }

  get isNotFound(): boolean {
    return this.status === 404;
  }

  get isValidationError(): boolean {
    return this.status === 422;
  }

  get isRateLimited(): boolean {
    return this.status === 429;
  }

  get isServerError(): boolean {
    return this.status !== undefined && this.status >= 500;
  }
}
