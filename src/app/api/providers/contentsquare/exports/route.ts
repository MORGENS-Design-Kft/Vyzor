import { NextRequest, NextResponse } from "next/server";
import { getProvider } from "@/lib/providers";
import { csCreateExportParamsSchema } from "@/lib/providers/contentsquare/schemas";
import { ApiError } from "@/lib/utils/api-error";

export async function GET() {
  try {
    const provider = await getProvider("contentsquare");
    const exports = await provider.listExports!();
    return NextResponse.json(exports);
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

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const validated = csCreateExportParamsSchema.parse(body);

    const provider = await getProvider("contentsquare");
    const result = await provider.createExport!(validated);
    return NextResponse.json(result, { status: 201 });
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
