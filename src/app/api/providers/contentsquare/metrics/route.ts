import { NextRequest, NextResponse } from "next/server";
import { getProvider } from "@/lib/providers";
import { csSiteMetricsQuerySchema } from "@/lib/providers/contentsquare/schemas";
import { ApiError } from "@/lib/utils/api-error";

export async function GET(request: NextRequest) {
  try {
    const params = Object.fromEntries(request.nextUrl.searchParams);
    const validated = csSiteMetricsQuerySchema.parse(params);

    const provider = await getProvider("contentsquare");
    const data = await provider.fetchMetrics!(validated);
    return NextResponse.json(data);
  } catch (error) {
    if (error instanceof ApiError) {
      return NextResponse.json(error.toJSON(), { status: error.status });
    }
    return NextResponse.json(
      { error: error instanceof Error ? error.message : "Unknown error" },
      { status: 400 },
    );
  }
}
