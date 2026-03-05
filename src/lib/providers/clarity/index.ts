import { BaseProvider } from "../base-provider";
import type {
  AuthState,
  NormalizedMetric,
  ProviderCapabilities,
  ProviderDataResponse,
  ProviderMeta,
} from "../types";
import type { ClarityInsightsParams, ClarityResponse } from "./types";
import { CLARITY_DIMENSIONS, CLARITY_METRICS } from "./types";
import { clarityResponseSchema } from "./schemas";
import { env } from "@/lib/config/env";

const CLARITY_API_BASE =
  "https://www.clarity.ms/export-data/api/v1";

export class ClarityProvider extends BaseProvider {
  meta: ProviderMeta = {
    id: "clarity",
    name: "Microsoft Clarity",
    description: "Heatmaps, session recordings, and behavioral analytics",
    docsUrl: "https://learn.microsoft.com/en-us/clarity/",
    color: "#6C2BD9",
  };

  capabilities: ProviderCapabilities = {
    canQueryLiveInsights: true,
    canCreateExports: false,
    canQueryMetrics: false,
    canListSegments: false,
    canListGoals: false,
    supportedDimensions: [...CLARITY_DIMENSIONS],
    supportedMetrics: [...CLARITY_METRICS],
    maxDimensions: 3,
    rateLimitPerDay: 10,
  };

  async authenticate(): Promise<AuthState> {
    const token = env.CLARITY_API_TOKEN;
    this.authState = {
      isAuthenticated: !!token,
      error: token ? undefined : "CLARITY_API_TOKEN not configured",
    };
    return this.authState;
  }

  async healthCheck(): Promise<{ healthy: boolean; message: string }> {
    if (!env.CLARITY_API_TOKEN) {
      return { healthy: false, message: "CLARITY_API_TOKEN not configured" };
    }
    try {
      await this.fetchInsights({ numOfDays: 1 });
      return { healthy: true, message: "Connected" };
    } catch (error) {
      return {
        healthy: false,
        message: error instanceof Error ? error.message : String(error),
      };
    }
  }

  async fetchInsights(
    params: ClarityInsightsParams,
  ): Promise<ProviderDataResponse> {
    const searchParams = new URLSearchParams({
      numOfDays: String(params.numOfDays),
    });
    if (params.dimension1) searchParams.set("dimension1", params.dimension1);
    if (params.dimension2) searchParams.set("dimension2", params.dimension2);
    if (params.dimension3) searchParams.set("dimension3", params.dimension3);

    const data = await this.apiFetch<ClarityResponse>(
      `${CLARITY_API_BASE}/project-live-insights?${searchParams}`,
      {},
      clarityResponseSchema,
    );

    return {
      providerId: "clarity",
      metrics: this.normalizeResponse(data),
      raw: data,
      fetchedAt: new Date().toISOString(),
    };
  }

  protected validateConfig(): void {
    // Clarity token is optional — provider reports unhealthy if missing
  }

  protected getAuthHeaders(): Record<string, string> {
    return env.CLARITY_API_TOKEN
      ? { Authorization: `Bearer ${env.CLARITY_API_TOKEN}` }
      : {};
  }

  private normalizeResponse(data: ClarityResponse): NormalizedMetric[] {
    return data.flatMap((group) =>
      group.information.map((info) => ({
        name: group.metricName,
        value: info.metricValue,
        dimensions: Object.fromEntries(
          Object.entries(info).filter(
            ([key]) => key !== "metricName" && key !== "metricValue",
          ),
        ) as Record<string, string>,
      })),
    );
  }
}
