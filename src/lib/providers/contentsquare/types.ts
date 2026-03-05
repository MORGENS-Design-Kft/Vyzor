export interface CSTokenResponse {
  access_token: string;
  token_type: string;
  expires_in: number;
  scope: string;
  project_id: number;
  endpoint: string;
}

export interface CSExportJob {
  id: number;
  name: string;
  status: string;
  createdAt: string;
  [key: string]: unknown;
}

export interface CSCreateExportParams {
  name: string;
  fields: string[];
  startDate: string;
  endDate: string;
  format?: "jsonl" | "csv";
}

export interface CSSiteMetricsParams {
  startDate: string;
  endDate: string;
  device?: string;
}

export interface CSSiteMetric {
  name: string;
  value: number;
  [key: string]: unknown;
}

export interface CSExportableField {
  name: string;
  type: string;
  description?: string;
}
