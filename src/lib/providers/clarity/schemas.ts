import { z } from "zod/v4";

export const clarityMetricInfoSchema = z.object({
  metricName: z.string(),
  metricValue: z.union([z.string(), z.number()]),
}).passthrough();

export const clarityMetricGroupSchema = z.object({
  metricName: z.string(),
  information: z.array(clarityMetricInfoSchema),
});

export const clarityResponseSchema = z.array(clarityMetricGroupSchema);

export const clarityInsightsQuerySchema = z.object({
  numOfDays: z.enum(["1", "2", "3"]),
  dimension1: z.string().optional(),
  dimension2: z.string().optional(),
  dimension3: z.string().optional(),
});
