import { NextResponse } from "next/server";
import { getRegisteredProviderIds, getProvider } from "@/lib/providers";

export async function GET() {
  const ids = getRegisteredProviderIds();

  const checks = await Promise.allSettled(
    ids.map(async (id) => {
      try {
        const provider = await getProvider(id);
        const health = await provider.healthCheck();
        return { id, ...health };
      } catch (error) {
        return {
          id,
          healthy: false,
          message: error instanceof Error ? error.message : "Failed",
        };
      }
    }),
  );

  const results = checks.map((result) =>
    result.status === "fulfilled"
      ? result.value
      : { id: "unknown", healthy: false, message: "Check failed" },
  );

  const allHealthy = results.every((r) => r.healthy);

  return NextResponse.json(
    {
      status: allHealthy ? "healthy" : "degraded",
      providers: results,
      timestamp: new Date().toISOString(),
    },
    { status: allHealthy ? 200 : 503 },
  );
}
