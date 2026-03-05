import { BaseProvider } from "../base-provider";
import type {
  AuthState,
  NormalizedMetric,
  ProviderCapabilities,
  ProviderDataResponse,
  ProviderMeta,
} from "../types";
import type {
  CSCreateExportParams,
  CSExportJob,
  CSSiteMetricsParams,
} from "./types";
import { ContentSquareAuthManager } from "./auth";
import { env } from "@/lib/config/env";

export class ContentSquareProvider extends BaseProvider {
  private authManager = new ContentSquareAuthManager();

  meta: ProviderMeta = {
    id: "contentsquare",
    name: "ContentSquare",
    description: "Digital experience analytics, session replay, and heatmaps",
    docsUrl: "https://docs.contentsquare.com/",
    color: "#3B28CC",
  };

  capabilities: ProviderCapabilities = {
    canQueryLiveInsights: false,
    canCreateExports: true,
    canQueryMetrics: true,
    canListSegments: true,
    canListGoals: true,
    supportedDimensions: [],
    supportedMetrics: [
      "bounceRate",
      "cartAverage",
      "conversionCount",
      "conversionRate",
      "pageviewAverage",
      "revenueSum",
      "sessionTimeAverage",
      "visits",
    ],
    maxDimensions: 0,
    rateLimitConcurrent: 10,
  };

  async authenticate(): Promise<AuthState> {
    if (!env.CONTENTSQUARE_CLIENT_ID || !env.CONTENTSQUARE_CLIENT_SECRET) {
      this.authState = {
        isAuthenticated: false,
        error: "ContentSquare credentials not configured",
      };
      return this.authState;
    }

    try {
      const tokenResponse = await this.authManager.getToken();
      this.authState = {
        isAuthenticated: true,
        expiresAt: new Date(
          Date.now() + tokenResponse.expires_in * 1000,
        ),
      };
    } catch (error) {
      this.authState = {
        isAuthenticated: false,
        error: error instanceof Error ? error.message : String(error),
      };
    }
    return this.authState;
  }

  async healthCheck(): Promise<{ healthy: boolean; message: string }> {
    if (!env.CONTENTSQUARE_CLIENT_ID || !env.CONTENTSQUARE_CLIENT_SECRET) {
      return {
        healthy: false,
        message: "ContentSquare credentials not configured",
      };
    }
    try {
      await this.authManager.getToken();
      return { healthy: true, message: "Connected" };
    } catch (error) {
      return {
        healthy: false,
        message: error instanceof Error ? error.message : String(error),
      };
    }
  }

  async fetchMetrics(
    params: CSSiteMetricsParams,
  ): Promise<ProviderDataResponse> {
    await this.ensureAuthenticated();
    const searchParams = new URLSearchParams({
      startDate: params.startDate,
      endDate: params.endDate,
    });
    if (params.device) searchParams.set("device", params.device);

    const data = await this.apiFetch<Record<string, unknown>>(
      `${this.authManager.endpoint}/v1/metrics/site?${searchParams}`,
    );

    return {
      providerId: "contentsquare",
      metrics: this.normalizeMetrics(data),
      raw: data,
      fetchedAt: new Date().toISOString(),
    };
  }

  async createExport(
    params: CSCreateExportParams,
  ): Promise<{ jobId: string }> {
    await this.ensureAuthenticated();
    const data = await this.apiFetch<{ id: number }>(
      `${this.authManager.endpoint}/v1/exports`,
      {
        method: "POST",
        body: JSON.stringify(params),
      },
    );
    return { jobId: String(data.id) };
  }

  async listExports(): Promise<CSExportJob[]> {
    await this.ensureAuthenticated();
    return this.apiFetch<CSExportJob[]>(
      `${this.authManager.endpoint}/v1/exports`,
    );
  }

  async getExportableFields(): Promise<unknown[]> {
    await this.ensureAuthenticated();
    return this.apiFetch<unknown[]>(
      `${this.authManager.endpoint}/v1/exportable-fields`,
    );
  }

  protected validateConfig(): void {
    // Credentials are optional — provider reports unhealthy if missing
  }

  protected getAuthHeaders(): Record<string, string> {
    try {
      return { Authorization: `Bearer ${this.authManager.currentToken}` };
    } catch {
      return {};
    }
  }

  private async ensureAuthenticated(): Promise<void> {
    if (!this.authState.isAuthenticated || this.authManager.isExpired()) {
      await this.authenticate();
    }
    if (!this.authState.isAuthenticated) {
      throw new Error(
        this.authState.error || "ContentSquare authentication failed",
      );
    }
  }

  private normalizeMetrics(
    data: Record<string, unknown>,
  ): NormalizedMetric[] {
    return Object.entries(data)
      .filter(([, value]) => typeof value === "number")
      .map(([name, value]) => ({
        name,
        value: value as number,
      }));
  }
}
