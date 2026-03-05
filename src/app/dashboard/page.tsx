"use client";

import { Header } from "@/components/layout/header";
import { ProviderCard } from "@/components/providers/provider-card";
import { useProviders } from "@/hooks/use-providers";
import { Skeleton } from "@/components/ui/skeleton";

export default function DashboardOverview() {
  const { data, isLoading, error } = useProviders();

  return (
    <div>
      <Header title="Overview" />
      <div className="p-6">
        <h3 className="mb-4 text-sm font-medium text-muted-foreground">
          Registered Providers
        </h3>

        {isLoading && (
          <div className="grid gap-4 md:grid-cols-2">
            <Skeleton className="h-40" />
            <Skeleton className="h-40" />
          </div>
        )}

        {error && (
          <p className="text-sm text-destructive">
            Failed to load providers: {error.message}
          </p>
        )}

        {data && (
          <div className="grid gap-4 md:grid-cols-2">
            {data.providers.map((provider) => (
              <ProviderCard key={provider.id} provider={provider} />
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
