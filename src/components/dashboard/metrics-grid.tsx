import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { NormalizedMetric } from "@/lib/providers/types";

export function MetricsGrid({ metrics }: { metrics: NormalizedMetric[] }) {
  if (metrics.length === 0) {
    return (
      <p className="text-sm text-muted-foreground">No metrics available.</p>
    );
  }

  return (
    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      {metrics.map((metric, i) => (
        <Card key={`${metric.name}-${i}`}>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">
              {metric.name}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold">
              {typeof metric.value === "number"
                ? metric.value.toLocaleString()
                : metric.value}
              {metric.unit && (
                <span className="ml-1 text-sm font-normal text-muted-foreground">
                  {metric.unit}
                </span>
              )}
            </p>
            {metric.dimensions &&
              Object.keys(metric.dimensions).length > 0 && (
                <div className="mt-2 flex flex-wrap gap-1">
                  {Object.entries(metric.dimensions).map(([key, val]) => (
                    <span
                      key={key}
                      className="rounded bg-muted px-1.5 py-0.5 text-xs text-muted-foreground"
                    >
                      {key}: {val}
                    </span>
                  ))}
                </div>
              )}
          </CardContent>
        </Card>
      ))}
    </div>
  );
}
