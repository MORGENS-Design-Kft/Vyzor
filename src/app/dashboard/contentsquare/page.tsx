"use client";

import { useState } from "react";
import { Header } from "@/components/layout/header";
import { MetricsGrid } from "@/components/dashboard/metrics-grid";
import { DataTable } from "@/components/dashboard/data-table";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { useCSMetrics, useCSExports, useCreateCSExport } from "@/hooks/use-contentsquare";

function getDateString(daysAgo: number): string {
  const d = new Date();
  d.setDate(d.getDate() - daysAgo);
  return d.toISOString().split("T")[0];
}

export default function ContentSquareDashboard() {
  const [metricsEnabled, setMetricsEnabled] = useState(false);
  const [exportsEnabled, setExportsEnabled] = useState(false);
  const [startDate] = useState(getDateString(30));
  const [endDate] = useState(getDateString(0));

  const {
    data: metricsData,
    isLoading: metricsLoading,
    error: metricsError,
    refetch: refetchMetrics,
  } = useCSMetrics({
    startDate,
    endDate,
    enabled: metricsEnabled,
  });

  const {
    data: exportsData,
    isLoading: exportsLoading,
    error: exportsError,
    refetch: refetchExports,
  } = useCSExports();

  const createExport = useCreateCSExport();

  const handleFetchMetrics = () => {
    setMetricsEnabled(true);
    refetchMetrics();
  };

  const handleFetchExports = () => {
    setExportsEnabled(true);
    refetchExports();
  };

  const handleCreateExport = () => {
    createExport.mutate({
      name: `Export ${new Date().toISOString()}`,
      fields: ["sessionId", "pageUrl", "timestamp"],
      startDate,
      endDate,
      format: "jsonl",
    });
  };

  const exportTableColumns = [
    { key: "id", label: "ID" },
    { key: "name", label: "Name" },
    { key: "status", label: "Status" },
    { key: "createdAt", label: "Created" },
  ];

  return (
    <div>
      <Header title="ContentSquare" />
      <div className="space-y-6 p-6">
        {/* Metrics Section */}
        <Card>
          <CardHeader>
            <CardTitle className="text-base">Site Metrics</CardTitle>
            <CardDescription>
              Aggregated site metrics for {startDate} to {endDate}
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <Button onClick={handleFetchMetrics} disabled={metricsLoading}>
              {metricsLoading ? "Fetching..." : "Fetch Metrics"}
            </Button>

            {metricsError && (
              <p className="text-sm text-destructive">{metricsError.message}</p>
            )}

            {metricsLoading && (
              <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {Array.from({ length: 4 }).map((_, i) => (
                  <Skeleton key={i} className="h-28" />
                ))}
              </div>
            )}

            {metricsData && <MetricsGrid metrics={metricsData.metrics} />}
          </CardContent>
        </Card>

        {/* Exports Section */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle className="text-base">Data Exports</CardTitle>
                <CardDescription>
                  Manage export jobs for detailed data extraction
                </CardDescription>
              </div>
              <div className="flex gap-2">
                <Button
                  variant="outline"
                  onClick={handleFetchExports}
                  disabled={exportsLoading}
                >
                  {exportsLoading ? "Loading..." : "Refresh"}
                </Button>
                <Button
                  onClick={handleCreateExport}
                  disabled={createExport.isPending}
                >
                  {createExport.isPending ? "Creating..." : "New Export"}
                </Button>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            {exportsError && (
              <p className="text-sm text-destructive">{exportsError.message}</p>
            )}

            {createExport.error && (
              <p className="mb-4 text-sm text-destructive">
                Export creation failed: {createExport.error.message}
              </p>
            )}

            {createExport.isSuccess && (
              <p className="mb-4 text-sm text-green-600">
                Export created with job ID: {createExport.data?.jobId}
              </p>
            )}

            {exportsEnabled && exportsData ? (
              <DataTable
                columns={exportTableColumns}
                data={exportsData as Record<string, unknown>[]}
                emptyMessage="No exports found. Create one to get started."
              />
            ) : (
              <div className="rounded-md border border-dashed p-8 text-center">
                <p className="text-sm text-muted-foreground">
                  Click &quot;Refresh&quot; to load existing exports, or
                  &quot;New Export&quot; to create one.
                </p>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
