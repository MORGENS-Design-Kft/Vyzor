import { z } from "zod/v4";

export const csTokenResponseSchema = z.object({
  access_token: z.string(),
  token_type: z.string(),
  expires_in: z.number(),
  scope: z.string(),
  project_id: z.number(),
  endpoint: z.string(),
});

export const csExportJobSchema = z
  .object({
    id: z.number(),
    name: z.string(),
    status: z.string(),
    createdAt: z.string(),
  })
  .passthrough();

export const csExportJobListSchema = z.array(csExportJobSchema);

export const csCreateExportParamsSchema = z.object({
  name: z.string().min(1),
  fields: z.array(z.string()).min(1),
  startDate: z.string(),
  endDate: z.string(),
  format: z.enum(["jsonl", "csv"]).optional(),
});

export const csSiteMetricsQuerySchema = z.object({
  startDate: z.string(),
  endDate: z.string(),
  device: z.string().optional(),
});
