"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import type { ProviderDataResponse } from "@/lib/providers/types";

export function useCSExports() {
  return useQuery<unknown[]>({
    queryKey: ["contentsquare", "exports"],
    queryFn: async () => {
      const res = await fetch("/api/providers/contentsquare/exports");
      if (!res.ok) {
        const error = await res.json();
        throw new Error(error.error || "Failed to fetch exports");
      }
      return res.json();
    },
  });
}

export function useCreateCSExport() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (params: {
      name: string;
      fields: string[];
      startDate: string;
      endDate: string;
      format?: "jsonl" | "csv";
    }) => {
      const res = await fetch("/api/providers/contentsquare/exports", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(params),
      });
      if (!res.ok) {
        const error = await res.json();
        throw new Error(error.error || "Failed to create export");
      }
      return res.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: ["contentsquare", "exports"],
      });
    },
  });
}

export function useCSMetrics(params: {
  startDate: string;
  endDate: string;
  device?: string;
  enabled?: boolean;
}) {
  const searchParams = new URLSearchParams({
    startDate: params.startDate,
    endDate: params.endDate,
  });
  if (params.device) searchParams.set("device", params.device);

  return useQuery<ProviderDataResponse>({
    queryKey: ["contentsquare", "metrics", params],
    queryFn: async () => {
      const res = await fetch(
        `/api/providers/contentsquare/metrics?${searchParams}`,
      );
      if (!res.ok) {
        const error = await res.json();
        throw new Error(error.error || "Failed to fetch metrics");
      }
      return res.json();
    },
    enabled: params.enabled ?? true,
    staleTime: 5 * 60 * 1000,
  });
}

export function useCSExportableFields() {
  return useQuery<unknown[]>({
    queryKey: ["contentsquare", "fields"],
    queryFn: async () => {
      const res = await fetch("/api/providers/contentsquare/fields");
      if (!res.ok) {
        const error = await res.json();
        throw new Error(error.error || "Failed to fetch fields");
      }
      return res.json();
    },
    staleTime: 30 * 60 * 1000,
  });
}
