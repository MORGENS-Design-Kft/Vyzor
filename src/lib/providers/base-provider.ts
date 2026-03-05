import type { z } from "zod/v4";
import type {
  AnalyticsProvider,
  AuthState,
  ProviderCapabilities,
  ProviderMeta,
} from "./types";
import { ApiError } from "@/lib/utils/api-error";

export abstract class BaseProvider implements AnalyticsProvider {
  abstract meta: ProviderMeta;
  abstract capabilities: ProviderCapabilities;

  protected authState: AuthState = { isAuthenticated: false };

  async initialize(): Promise<void> {
    this.validateConfig();
    await this.authenticate();
  }

  abstract authenticate(): Promise<AuthState>;
  abstract healthCheck(): Promise<{ healthy: boolean; message: string }>;

  protected abstract validateConfig(): void;
  protected abstract getAuthHeaders(): Record<string, string>;

  protected async apiFetch<T>(
    url: string,
    options: RequestInit = {},
    schema?: z.ZodType<T>,
  ): Promise<T> {
    const response = await fetch(url, {
      ...options,
      headers: {
        "Content-Type": "application/json",
        ...this.getAuthHeaders(),
        ...options.headers,
      },
    });

    if (!response.ok) {
      throw ApiError.fromResponse(
        response.status,
        response.statusText,
        this.meta.id,
      );
    }

    const data = await response.json();
    return schema ? schema.parse(data) : (data as T);
  }
}
