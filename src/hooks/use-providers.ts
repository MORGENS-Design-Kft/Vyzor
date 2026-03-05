"use client";

import { useQuery } from "@tanstack/react-query";

export interface ProviderStatus {
  id: string;
  name: string;
  description: string;
  docsUrl: string;
  color: string;
  capabilities: {
    canQueryLiveInsights: boolean;
    canCreateExports: boolean;
    canQueryMetrics: boolean;
    supportedDimensions: string[];
    supportedMetrics: string[];
  };
  status: {
    id: string;
    healthy: boolean;
    message: string;
  };
}

export function useProviders() {
  return useQuery<{ providers: ProviderStatus[] }>({
    queryKey: ["providers"],
    queryFn: async () => {
      const res = await fetch("/api/providers");
      if (!res.ok) throw new Error("Failed to fetch providers");
      return res.json();
    },
    staleTime: 5 * 60 * 1000,
  });
}
