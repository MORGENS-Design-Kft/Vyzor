import { z } from "zod/v4";

// Transform empty strings to undefined so optional env vars work correctly
const optionalString = z
  .string()
  .transform((val) => (val === "" ? undefined : val))
  .pipe(z.string().optional());

const envSchema = z.object({
  CLARITY_API_TOKEN: optionalString,
  CONTENTSQUARE_CLIENT_ID: optionalString,
  CONTENTSQUARE_CLIENT_SECRET: optionalString,
});

export type Env = z.infer<typeof envSchema>;

function getEnv(): Env {
  const parsed = envSchema.safeParse(process.env);
  if (!parsed.success) {
    console.error("Invalid environment variables:", parsed.error.flatten());
    throw new Error("Invalid environment configuration");
  }
  return parsed.data;
}

export const env = getEnv();
