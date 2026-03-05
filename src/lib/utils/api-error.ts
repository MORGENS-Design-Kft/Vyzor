export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
    public providerId: string,
    public code?: string,
    public retryAfter?: number,
  ) {
    super(message);
    this.name = "ApiError";
  }

  static fromResponse(
    status: number,
    statusText: string,
    providerId: string,
  ): ApiError {
    const isRateLimit = status === 429;
    return new ApiError(
      `${providerId} API error: ${status} ${statusText}`,
      status,
      providerId,
      isRateLimit ? "RATE_LIMITED" : `HTTP_${status}`,
      isRateLimit ? 86400 : undefined,
    );
  }

  toJSON() {
    return {
      error: this.message,
      status: this.status,
      provider: this.providerId,
      code: this.code,
      retryAfter: this.retryAfter,
    };
  }
}
