"use client";

import { useQuery } from "@tanstack/react-query";
import type { ProviderDataResponse } from "@/lib/providers/types";

interface ClarityInsightsParams {
  numOfDays: 1 | 2 | 3;
  dimensions?: string[];
  enabled?: boolean;
}

export function useClarityInsights({
  numOfDays,
  dimensions = [],
  enabled = true,
}: ClarityInsightsParams) {
  const searchParams = new URLSearchParams({
    numOfDays: String(numOfDays),
  });
  dimensions.forEach((dim, i) => {
    searchParams.set(`dimension${i + 1}`, dim);
  });

  return useQuery<ProviderDataResponse>({
    queryKey: ["clarity", "insights", numOfDays, dimensions],
    queryFn: async () => {
      const res = await fetch(`/api/providers/clarity?${searchParams}`);
      if (!res.ok) {
        const error = await res.json();
        throw new Error(error.error || "Clarity fetch failed");
      }
      return res.json();
    },
    enabled,
    staleTime: 10 * 60 * 1000,
  });
}
