import { NextResponse } from "next/server";
import {
  getProviderMeta,
  getRegisteredProviderIds,
  getProvider,
} from "@/lib/providers";

export async function GET() {
  const metas = getProviderMeta();
  const ids = getRegisteredProviderIds();

  const statuses = await Promise.allSettled(
    ids.map(async (id) => {
      try {
        const provider = await getProvider(id);
        return { id, ...(await provider.healthCheck()) };
      } catch (error) {
        return {
          id,
          healthy: false,
          message: error instanceof Error ? error.message : "Failed to initialize",
        };
      }
    }),
  );

  const providers = metas.map((meta, i) => {
    const result = statuses[i];
    const status =
      result.status === "fulfilled"
        ? result.value
        : { id: meta.id, healthy: false, message: "Failed to initialize" };
    return { ...meta, status };
  });

  return NextResponse.json({ providers });
}
