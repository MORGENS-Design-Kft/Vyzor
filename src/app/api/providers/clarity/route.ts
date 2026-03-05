import { NextRequest, NextResponse } from "next/server";
import { getProvider } from "@/lib/providers";
import { clarityInsightsQuerySchema } from "@/lib/providers/clarity/schemas";
import { ApiError } from "@/lib/utils/api-error";

export async function GET(request: NextRequest) {
  try {
    const params = Object.fromEntries(request.nextUrl.searchParams);
    const validated = clarityInsightsQuerySchema.parse(params);

    const provider = await getProvider("clarity");
    const data = await provider.fetchInsights!({
      numOfDays: Number(validated.numOfDays),
      dimension1: validated.dimension1,
      dimension2: validated.dimension2,
      dimension3: validated.dimension3,
    });

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
