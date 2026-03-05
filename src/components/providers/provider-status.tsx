import { cn } from "@/lib/utils";

export function ProviderStatusDot({
  healthy,
  className,
}: {
  healthy: boolean;
  className?: string;
}) {
  return (
    <span
      className={cn(
        "inline-block h-2.5 w-2.5 rounded-full",
        healthy ? "bg-green-500" : "bg-red-500",
        className,
      )}
    />
  );
}
