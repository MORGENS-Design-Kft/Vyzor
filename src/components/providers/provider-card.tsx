import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { ProviderStatusDot } from "./provider-status";
import type { ProviderStatus } from "@/hooks/use-providers";

export function ProviderCard({ provider }: { provider: ProviderStatus }) {
  const capabilities = [];
  if (provider.capabilities.canQueryLiveInsights)
    capabilities.push("Live Insights");
  if (provider.capabilities.canCreateExports) capabilities.push("Exports");
  if (provider.capabilities.canQueryMetrics) capabilities.push("Metrics");

  return (
    <Card>
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <CardTitle className="text-base">{provider.name}</CardTitle>
          <div className="flex items-center gap-2">
            <ProviderStatusDot healthy={provider.status.healthy} />
            <span className="text-xs text-muted-foreground">
              {provider.status.healthy ? "Connected" : "Disconnected"}
            </span>
          </div>
        </div>
        <CardDescription>{provider.description}</CardDescription>
      </CardHeader>
      <CardContent>
        <div className="flex flex-wrap gap-1.5">
          {capabilities.map((cap) => (
            <Badge key={cap} variant="secondary" className="text-xs">
              {cap}
            </Badge>
          ))}
        </div>
        <p className="mt-3 text-xs text-muted-foreground">
          {provider.status.message}
        </p>
      </CardContent>
    </Card>
  );
}
