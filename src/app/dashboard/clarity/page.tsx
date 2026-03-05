"use client";

import { useState } from "react";
import { Header } from "@/components/layout/header";
import { MetricsGrid } from "@/components/dashboard/metrics-grid";
import { DimensionPicker } from "@/components/dashboard/dimension-picker";
import { DataTable } from "@/components/dashboard/data-table";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Skeleton } from "@/components/ui/skeleton";
import { useClarityInsights } from "@/hooks/use-clarity";

export default function ClarityDashboard() {
  const [numOfDays, setNumOfDays] = useState<1 | 2 | 3>(1);
  const [dim1, setDim1] = useState("none");
  const [dim2, setDim2] = useState("none");
  const [dim3, setDim3] = useState("none");
  const [fetchEnabled, setFetchEnabled] = useState(false);

  const dimensions = [dim1, dim2, dim3].filter((d) => d !== "none");

  const { data, isLoading, error, refetch } = useClarityInsights({
    numOfDays,
    dimensions,
    enabled: fetchEnabled,
  });

  const handleFetch = () => {
    setFetchEnabled(true);
    refetch();
  };

  const rawData = (data?.raw as Array<Record<string, unknown>>) ?? [];
  const tableData = rawData.flatMap((group: Record<string, unknown>) => {
    const info = group.information as Array<Record<string, unknown>> | undefined;
    return (info ?? []).map((item) => ({
      metric: group.metricName,
      ...item,
    }));
  });

  const tableColumns = tableData.length > 0
    ? Object.keys(tableData[0]).map((key) => ({ key, label: key }))
    : [{ key: "metric", label: "Metric" }];

  return (
    <div>
      <Header title="Microsoft Clarity" />
      <div className="space-y-6 p-6">
        <div className="flex flex-wrap items-end gap-4">
          <div className="space-y-1.5">
            <label className="text-sm font-medium">Time Range</label>
            <Select
              value={String(numOfDays)}
              onValueChange={(v) => setNumOfDays(Number(v) as 1 | 2 | 3)}
            >
              <SelectTrigger className="w-[200px]">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="1">Last 24 hours</SelectItem>
                <SelectItem value="2">Last 48 hours</SelectItem>
                <SelectItem value="3">Last 72 hours</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <DimensionPicker label="Dimension 1" value={dim1} onChange={setDim1} />
          <DimensionPicker label="Dimension 2" value={dim2} onChange={setDim2} />
          <DimensionPicker label="Dimension 3" value={dim3} onChange={setDim3} />

          <Button onClick={handleFetch} disabled={isLoading}>
            {isLoading ? "Fetching..." : "Fetch Insights"}
          </Button>
        </div>

        {error && (
          <div className="rounded-md border border-destructive/50 bg-destructive/10 p-4">
            <p className="text-sm text-destructive">{error.message}</p>
          </div>
        )}

        {isLoading && (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {Array.from({ length: 4 }).map((_, i) => (
              <Skeleton key={i} className="h-28" />
            ))}
          </div>
        )}

        {data && (
          <>
            <div>
              <h3 className="mb-3 text-sm font-medium text-muted-foreground">
                Metrics Summary ({data.metrics.length} data points)
              </h3>
              <MetricsGrid metrics={data.metrics.slice(0, 12)} />
            </div>

            <div>
              <h3 className="mb-3 text-sm font-medium text-muted-foreground">
                Raw Data ({tableData.length} rows)
              </h3>
              <DataTable
                columns={tableColumns}
                data={tableData}
                emptyMessage="No insights data returned. Check your API token."
              />
            </div>
          </>
        )}

        {!fetchEnabled && !isLoading && (
          <div className="rounded-md border border-dashed p-12 text-center">
            <p className="text-sm text-muted-foreground">
              Configure your parameters and click &quot;Fetch Insights&quot; to
              load data from Microsoft Clarity.
            </p>
            <p className="mt-2 text-xs text-muted-foreground">
              Rate limit: 10 requests per project per day.
            </p>
          </div>
        )}
      </div>
    </div>
  );
}
