import { NextResponse } from "next/server";
import { getProvider } from "@/lib/providers";
import { ApiError } from "@/lib/utils/api-error";

export async function GET() {
  try {
    const provider = await getProvider("contentsquare");
    const fields = await provider.getExportableFields!();
    return NextResponse.json(fields);
  } catch (error) {
    if (error instanceof ApiError) {
      return NextResponse.json(error.toJSON(), { status: error.status });
    }
    return NextResponse.json(
      { error: error instanceof Error ? error.message : "Unknown error" },
      { status: 500 },
    );
  }
}
