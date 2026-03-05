export type ProviderId = "clarity" | "contentsquare";

export interface ProviderMeta {
  id: ProviderId;
  name: string;
  description: string;
  docsUrl: string;
  color: string;
}

export type AuthStrategy = "bearer-token" | "oauth2-client-credentials";

export interface AuthState {
  isAuthenticated: boolean;
  expiresAt?: Date;
  error?: string;
}

export interface NormalizedMetric {
  name: string;
  value: number | string;
  unit?: string;
  dimensions?: Record<string, string>;
  timestamp?: string;
}

export interface ProviderDataResponse {
  providerId: ProviderId;
  metrics: NormalizedMetric[];
  raw: unknown;
  fetchedAt: string;
  rateLimitRemaining?: number;
}

export interface ProviderCapabilities {
  canQueryLiveInsights: boolean;
  canCreateExports: boolean;
  canQueryMetrics: boolean;
  canListSegments: boolean;
  canListGoals: boolean;
  supportedDimensions: string[];
  supportedMetrics: string[];
  maxDimensions: number;
  rateLimitPerDay?: number;
  rateLimitConcurrent?: number;
}

export interface AnalyticsProvider {
  meta: ProviderMeta;
  capabilities: ProviderCapabilities;

  initialize(): Promise<void>;
  authenticate(): Promise<AuthState>;
  healthCheck(): Promise<{ healthy: boolean; message: string }>;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  fetchInsights?(params: any): Promise<ProviderDataResponse>;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  createExport?(params: any): Promise<{ jobId: string }>;
  listExports?(): Promise<unknown[]>;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  fetchMetrics?(params: any): Promise<ProviderDataResponse>;
  getExportableFields?(): Promise<unknown[]>;
}
